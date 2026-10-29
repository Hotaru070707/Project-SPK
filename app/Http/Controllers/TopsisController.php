<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TopsisCriterion;
use App\Models\TopsisResult;
use App\Services\TopsisCalculationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TopsisController extends Controller
{
    private const CRITERIA_CODES = ['C1', 'C2', 'C3'];

    /**
     * @var array<string, array{name: string, type: string, data_source: string}>
     */
    private const CRITERIA_CONFIGURATION = [
        'C1' => [
            'name' => 'Tingkat Penjualan',
            'type' => 'benefit',
            'data_source' => 'order_items.qty',
        ],
        'C2' => [
            'name' => 'Stok Tersedia',
            'type' => 'cost',
            'data_source' => 'products.stock',
        ],
        'C3' => [
            'name' => 'Waktu Pengadaan',
            'type' => 'cost',
            'data_source' => 'products.supplier_lead_time_days',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const ALTERNATIVES = [
        'Beras' => 'beras',
        'Minyak Goreng' => 'minyak goreng',
        'Telur Ayam' => 'telur ayam',
        'Kecap Manis' => 'kecap manis',
    ];

    public function criteria(): View
    {
        $criteria = TopsisCriterion::query()
            ->where('is_active', true)
            ->whereIn('code', self::CRITERIA_CODES)
            ->orderBy('code')
            ->get();
        $totalWeight = $criteria->sum('weight');
        [$products, $missingAlternatives] = $this->resolveAlternatives();
        $productsMissingLeadTime = $products
            ->filter(fn (Product $product): bool => $product->supplier_lead_time_days === null);
        $productsWithInvalidStock = $products
            ->filter(fn (Product $product): bool => $product->stock < 0);
        $criteriaValid = $this->hasValidCriteria($criteria)
            && $criteria->sum(fn (TopsisCriterion $criterion): int => (int) round((float) $criterion->weight * 10000)) === 10000;
        $dataComplete = $missingAlternatives === []
            && $products->count() === count(self::ALTERNATIVES)
            && $productsMissingLeadTime->isEmpty()
            && $productsWithInvalidStock->isEmpty();
        $latestResult = TopsisResult::query()->latest('created_at')->latest('id')->first();

        return view('owner.topsis.criteria', compact(
            'criteria',
            'totalWeight',
            'criteriaValid',
            'dataComplete',
            'missingAlternatives',
            'productsMissingLeadTime',
            'productsWithInvalidStock',
            'latestResult'
        ));
    }

    public function updateCriteria(Request $request): RedirectResponse
    {
        $validated = $this->validatedWeights($request);
        $criteria = TopsisCriterion::query()
            ->where('is_active', true)
            ->whereIn('code', self::CRITERIA_CODES)
            ->orderBy('code')
            ->get();
        if (! $this->hasValidCriteria($criteria)) {
            throw ValidationException::withMessages([
                'weights' => ['Tiga kriteria TOPSIS yang diwajibkan belum tersedia atau tipenya tidak sesuai.'],
            ]);
        }

        $expectedIds = array_map('strval', $criteria->modelKeys());
        $submittedIds = array_map('strval', array_keys($validated['weights']));
        sort($expectedIds);
        sort($submittedIds);

        if ($expectedIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'weights' => ['Semua bobot kriteria harus dikirim dan tidak boleh ada kriteria asing.'],
            ]);
        }

        DB::transaction(function () use ($validated): void {
            foreach ($validated['weights'] as $id => $weight) {
                TopsisCriterion::query()
                    ->whereKey($id)
                    ->where('is_active', true)
                    ->whereIn('code', self::CRITERIA_CODES)
                    ->update(['weight' => $weight]);
            }
        });

        return redirect()
            ->route('admin.topsis.criteria')
            ->with('success', 'Bobot kriteria TOPSIS berhasil diperbarui.');
    }

    public function calculate(): View|RedirectResponse
    {
        $result = TopsisResult::query()->latest('created_at')->latest('id')->first();
        if (! $result) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('info', 'Belum ada hasil perhitungan TOPSIS. Jalankan perhitungan untuk membuat hasil pertama.');
        }

        return $this->calculationView($result);
    }

    public function runCalculation(TopsisCalculationService $calculationService): RedirectResponse
    {
        $criteria = TopsisCriterion::query()
            ->where('is_active', true)
            ->whereIn('code', self::CRITERIA_CODES)
            ->orderBy('code')
            ->get();
        if (! $this->hasValidCriteria($criteria)) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Konfigurasi kriteria TOPSIS tidak sesuai dengan sumber, nama, atau tipe yang diwajibkan.');
        }

        $weightUnits = $criteria->sum(fn (TopsisCriterion $criterion): int => (int) round((float) $criterion->weight * 10000));
        if ($weightUnits !== 10000) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Total bobot kriteria harus tepat 100% sebelum perhitungan.');
        }

        [$products, $missingAlternatives] = $this->resolveAlternatives();
        if ($missingAlternatives !== []) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Produk alternatif belum tersedia atau tidak aktif: '.implode(', ', $missingAlternatives).'.');
        }

        if ($products->count() !== count(self::ALTERNATIVES)) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Empat produk alternatif yang diwajibkan belum tersedia di database.');
        }

        $productsMissingLeadTime = $products
            ->filter(fn (Product $product): bool => $product->supplier_lead_time_days === null);
        if ($productsMissingLeadTime->isNotEmpty()) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Lengkapi waktu pengadaan supplier untuk: '.$productsMissingLeadTime->pluck('name')->implode(', ').'.');
        }

        $productsWithInvalidValues = $products->filter(
            fn (Product $product): bool => $product->stock < 0 || $product->supplier_lead_time_days < 0
        );
        if ($productsWithInvalidValues->isNotEmpty()) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Perbaiki data stok atau waktu pengadaan yang bernilai negatif untuk: '.$productsWithInvalidValues->pluck('name')->implode(', ').'.');
        }

        $periodEnd = now();
        $periodStart = $periodEnd->copy()->subDays(30);
        $salesByProduct = OrderItem::query()
            ->select('product_id')
            ->selectRaw('SUM(qty) as units_sold')
            ->whereIn('product_id', $products->pluck('id'))
            ->where('item_status', 'normal')
            ->where('qty', '>', 0)
            ->whereHas('order', function ($query) use ($periodStart, $periodEnd): void {
                $query->where('payment_status', 'paid')
                    ->whereNotIn('order_status', ['cancelled', 'void'])
                    ->whereBetween('paid_at', [$periodStart, $periodEnd]);
            })
            ->groupBy('product_id')
            ->pluck('units_sold', 'product_id');

        $matrixX = [];
        foreach ($products as $product) {
            $matrixX[$product->id] = [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'stock' => $product->stock,
                    'min_stock' => $product->min_stock,
                    'unit' => $product->unit,
                    'supplier_lead_time_days' => $product->supplier_lead_time_days,
                ],
                'C1' => (float) ($salesByProduct[$product->id] ?? 0),
                'C2' => (float) $product->stock,
                'C3' => (float) $product->supplier_lead_time_days,
            ];
        }

        $criteriaSnapshot = $criteria->map(fn (TopsisCriterion $criterion): array => [
            'code' => $criterion->code,
            'name' => $criterion->name,
            'description' => $criterion->description,
            'type' => $criterion->type,
            'weight' => (float) $criterion->weight,
            'data_source' => $criterion->data_source,
        ])->all();
        $calculation = $calculationService->calculate($criteriaSnapshot, $matrixX);
        $criteriaSnapshotPayload = [
            'criteria' => $criteriaSnapshot,
            'zero_divisor_codes' => $calculation['zero_divisor_codes'],
            'alternatives_identical' => $calculation['alternatives_identical'],
            'weight_total' => $weightUnits / 10000,
        ];

        DB::transaction(function () use (
            $periodStart,
            $periodEnd,
            $criteriaSnapshotPayload,
            $matrixX,
            $calculation
        ): void {
            TopsisResult::query()->create([
                'period_start' => $periodStart->toDateTimeString(),
                'period_end' => $periodEnd->toDateTimeString(),
                'generated_by' => Auth::id(),
                'criteria_snapshot' => $criteriaSnapshotPayload,
                'decision_matrix' => $matrixX,
                'normalized_matrix' => $calculation['normalized_matrix'],
                'weighted_matrix' => $calculation['weighted_matrix'],
                'ideal_positive' => $calculation['ideal_positive'],
                'ideal_negative' => $calculation['ideal_negative'],
                'distances' => $calculation['distances'],
                'preferences' => $calculation['preferences'],
            ]);
        });

        return redirect()
            ->route('admin.topsis.calculate')
            ->with('success', 'Perhitungan TOPSIS berhasil disimpan ke riwayat.');
    }

    public function recommendations(): View|RedirectResponse
    {
        $latestResult = TopsisResult::query()->latest('created_at')->latest('id')->first();
        if (! $latestResult) {
            return redirect()->route('admin.topsis.criteria');
        }

        $criteriaSnapshot = $latestResult->criteria_snapshot;
        $criteria = $this->criteriaFromSnapshot($criteriaSnapshot);
        $decisionMatrix = $latestResult->decision_matrix;
        $preferences = [];
        $position = 1;

        foreach ($latestResult->preferences as $productId => $item) {
            $item['rank'] ??= $position;
            $item['metrics'] = $decisionMatrix[$productId] ?? [];
            $preferences[] = $item;
            $position++;
        }

        $isLegacyResult = array_column($criteria, 'code') !== self::CRITERIA_CODES;

        return view('owner.topsis.recommendations', compact(
            'preferences',
            'criteria',
            'latestResult',
            'isLegacyResult'
        ));
    }

    public function history(): View
    {
        $results = TopsisResult::query()
            ->with('generator:id,name')
            ->latest('created_at')
            ->latest('id')
            ->paginate(15);

        return view('owner.topsis.history', compact('results'));
    }

    public function showHistory(TopsisResult $result): View
    {
        return $this->calculationView($result->load('generator:id,name'));
    }

    private function calculationView(TopsisResult $result): View
    {
        $criteriaSnapshot = $result->criteria_snapshot;
        $criteria = $this->criteriaFromSnapshot($criteriaSnapshot);
        $zeroDivisorCodes = $criteriaSnapshot['zero_divisor_codes'] ?? [];
        $alternativesIdentical = $criteriaSnapshot['alternatives_identical'] ?? false;
        $matrixX = $result->decision_matrix;
        $matrixR = $result->normalized_matrix;
        $matrixY = $result->weighted_matrix;
        $idealPositive = $result->ideal_positive;
        $idealNegative = $result->ideal_negative;
        $distances = $result->distances;
        $preferences = $result->preferences;
        $position = 1;
        foreach ($preferences as &$preference) {
            $productId = $preference['product']['id'] ?? null;
            $preference['rank'] ??= $position;
            $preference['d_plus'] ??= $distances[$productId]['d_plus'] ?? 0;
            $preference['d_minus'] ??= $distances[$productId]['d_minus'] ?? 0;
            $position++;
        }
        unset($preference);

        return view('owner.topsis.calculation', compact(
            'result',
            'criteria',
            'matrixX',
            'matrixR',
            'matrixY',
            'idealPositive',
            'idealNegative',
            'distances',
            'preferences',
            'zeroDivisorCodes',
            'alternativesIdentical'
        ));
    }

    /**
     * @return array{0: Collection<int, Product>, 1: array<int, string>}
     */
    private function resolveAlternatives(): array
    {
        $activeProducts = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'stock',
                'min_stock',
                'unit',
                'supplier_lead_time_days',
            ]);
        $products = collect();
        $missingAlternatives = [];

        foreach (self::ALTERNATIVES as $label => $searchTerm) {
            $searchTerm = Str::lower($searchTerm);
            $product = $activeProducts->first(
                fn (Product $candidate): bool => Str::contains(Str::lower($candidate->name), $searchTerm)
            );

            if ($product === null) {
                $missingAlternatives[] = $label;

                continue;
            }

            $products->push($product);
        }

        return [$products, $missingAlternatives];
    }

    /**
     * @param  Collection<int, TopsisCriterion>  $criteria
     */
    private function hasValidCriteria(Collection $criteria): bool
    {
        if ($criteria->pluck('code')->all() !== self::CRITERIA_CODES) {
            return false;
        }

        foreach ($criteria as $criterion) {
            $expected = self::CRITERIA_CONFIGURATION[$criterion->code] ?? null;
            if (
                $expected === null
                || $criterion->name !== $expected['name']
                || $criterion->type !== $expected['type']
                || $criterion->data_source !== $expected['data_source']
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{weights: array<int, string|int|float>}
     */
    private function validatedWeights(Request $request): array
    {
        $validated = $request->validate([
            'weights' => ['required', 'array'],
            'weights.*' => ['required', 'numeric', 'decimal:0,4', 'min:0', 'max:1'],
        ]);

        $criteria = TopsisCriterion::query()
            ->where('is_active', true)
            ->whereIn('code', self::CRITERIA_CODES)
            ->orderBy('code')
            ->get();

        if (! $this->hasValidCriteria($criteria)) {
            throw ValidationException::withMessages([
                'weights' => ['Konfigurasi tiga kriteria TOPSIS belum sesuai. Jalankan migration terbaru sebelum menyimpan bobot.'],
            ]);
        }

        $expectedIds = array_map('strval', $criteria->modelKeys());
        $submittedIds = array_map('strval', array_keys($validated['weights']));
        sort($expectedIds);
        sort($submittedIds);

        if ($expectedIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'weights' => ['Semua bobot kriteria harus dikirim dan tidak boleh ada kriteria asing.'],
            ]);
        }

        $totalWeightUnits = array_sum(array_map(
            static fn (int|float|string $weight): int => (int) round((float) $weight * 10000),
            $validated['weights']
        ));
        if ($totalWeightUnits !== 10000) {
            throw ValidationException::withMessages([
                'weights' => ['Total bobot kriteria harus tepat 1,00 (100%).'],
            ]);
        }

        return $validated;
    }

    /**
     * @param  array<string|int, mixed>  $snapshot
     * @return array<int, array<string, mixed>>
     */
    private function criteriaFromSnapshot(array $snapshot): array
    {
        if (isset($snapshot['criteria']) && is_array($snapshot['criteria'])) {
            return $snapshot['criteria'];
        }

        return array_values($snapshot);
    }
}
