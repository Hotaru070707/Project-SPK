<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TopsisCriterion;
use App\Models\TopsisResult;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TopsisController extends Controller
{
    public function criteria(): View
    {
        $criteria = TopsisCriterion::query()->orderBy('code')->get();
        $totalWeight = $criteria->sum('weight');

        return view('owner.topsis.criteria', compact('criteria', 'totalWeight'));
    }

    public function updateCriteria(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weights' => ['required', 'array'],
            'weights.*' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        $criteria = TopsisCriterion::query()->get();
        $expectedIds = array_map('strval', $criteria->modelKeys());
        $submittedIds = array_map('strval', array_keys($validated['weights']));
        sort($expectedIds);
        sort($submittedIds);

        if ($expectedIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'weights' => ['Semua bobot kriteria harus dikirim dan tidak boleh ada kriteria asing.'],
            ]);
        }

        $totalWeight = array_sum(array_map('floatval', $validated['weights']));
        if (abs($totalWeight - 1.0) > 0.0001) {
            throw ValidationException::withMessages([
                'weights' => ['Total bobot kriteria harus tepat 1,00 (100%).'],
            ]);
        }

        DB::transaction(function () use ($validated): void {
            foreach ($validated['weights'] as $id => $weight) {
                TopsisCriterion::query()->whereKey($id)->update(['weight' => $weight]);
            }
        });

        return redirect()
            ->route('admin.topsis.criteria')
            ->with('success', 'Bobot kriteria TOPSIS berhasil diperbarui.');
    }

    public function calculate(): View|RedirectResponse
    {
        $criteria = TopsisCriterion::query()
            ->where('is_active', true)
            ->whereIn('code', ['C1', 'C2', 'C3', 'C4'])
            ->orderBy('code')
            ->get();
        $criteriaCodes = $criteria->pluck('code')->all();
        if ($criteriaCodes !== ['C1', 'C2', 'C3', 'C4']) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Empat kriteria TOPSIS belum tersedia atau belum aktif.');
        }

        $totalWeight = $criteria->sum('weight');
        if (abs($totalWeight - 1.0) > 0.0001) {
            return redirect()
                ->route('admin.topsis.criteria')
                ->with('error', 'Total bobot kriteria harus tepat 100% sebelum perhitungan.');
        }

        $products = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        if ($products->count() < 2) {
            return redirect()
                ->route('admin.products')
                ->with('error', 'Dibutuhkan minimal dua produk aktif untuk melakukan analisis TOPSIS.');
        }

        $productsMissingLeadTime = $products
            ->filter(fn (Product $product): bool => $product->supplier_lead_time_days === null)
            ->pluck('name');
        if ($productsMissingLeadTime->isNotEmpty()) {
            return redirect()
                ->route('admin.products')
                ->with('error', 'Lengkapi waktu pengadaan pemasok untuk produk berikut sebelum menghitung TOPSIS: '.$productsMissingLeadTime->implode(', '));
        }

        $periodEnd = now();
        $periodStart = $periodEnd->copy()->subDays(30)->startOfDay();
        $salesByProduct = OrderItem::query()
            ->select('product_id')
            ->selectRaw('SUM(qty) as units_sold')
            ->whereIn('product_id', $products->modelKeys())
            ->whereHas('order', function ($query) use ($periodStart, $periodEnd): void {
                $query->where('payment_status', 'paid')
                    ->whereBetween('paid_at', [$periodStart, $periodEnd]);
            })
            ->groupBy('product_id')
            ->pluck('units_sold', 'product_id');

        $matrixX = [];
        foreach ($products as $product) {
            $unitsSold = (float) ($salesByProduct[$product->id] ?? 0);
            $stockUrgency = $product->stock <= $product->min_stock
                ? ($product->min_stock + 1 - $product->stock) / max(1, $product->min_stock + 1)
                : 0.0;
            $profitMargin = $product->sell_price > 0
                ? (($product->sell_price - $product->cost_price) / $product->sell_price) * 100
                : 0.0;

            $matrixX[$product->id] = [
                'product' => $product,
                'C1' => (float) $stockUrgency,
                'C2' => $unitsSold,
                'C3' => (float) $product->supplier_lead_time_days,
                'C4' => (float) $profitMargin,
            ];
        }

        $divisors = [];
        foreach ($criteria as $criterion) {
            $code = $criterion->code;
            $sumOfSquares = 0.0;
            foreach ($matrixX as $row) {
                $sumOfSquares += $row[$code] ** 2;
            }
            $divisors[$code] = sqrt($sumOfSquares) ?: 1.0;
        }

        $matrixR = [];
        foreach ($matrixX as $productId => $row) {
            $matrixR[$productId] = ['product' => $row['product']];
            foreach ($criteria as $criterion) {
                $code = $criterion->code;
                $matrixR[$productId][$code] = $row[$code] / $divisors[$code];
            }
        }

        $matrixY = [];
        foreach ($matrixR as $productId => $row) {
            $matrixY[$productId] = ['product' => $row['product']];
            foreach ($criteria as $criterion) {
                $code = $criterion->code;
                $matrixY[$productId][$code] = $row[$code] * (float) $criterion->weight;
            }
        }

        $idealPositive = [];
        $idealNegative = [];
        foreach ($criteria as $criterion) {
            $code = $criterion->code;
            $columnValues = array_column($matrixY, $code);
            if ($criterion->type === 'benefit') {
                $idealPositive[$code] = max($columnValues);
                $idealNegative[$code] = min($columnValues);
            } else {
                $idealPositive[$code] = min($columnValues);
                $idealNegative[$code] = max($columnValues);
            }
        }

        $distances = [];
        $preferences = [];
        foreach ($matrixY as $productId => $row) {
            $dPlusSquared = 0.0;
            $dMinusSquared = 0.0;
            foreach ($criteria as $criterion) {
                $code = $criterion->code;
                $dPlusSquared += ($row[$code] - $idealPositive[$code]) ** 2;
                $dMinusSquared += ($row[$code] - $idealNegative[$code]) ** 2;
            }

            $dPlus = sqrt($dPlusSquared);
            $dMinus = sqrt($dMinusSquared);
            $preference = ($dPlus + $dMinus) > 0
                ? $dMinus / ($dPlus + $dMinus)
                : 0.5;
            $product = $row['product'];
            $unitsSold = (int) $matrixX[$productId]['C2'];
            $isUrgent = $product->stock <= $product->min_stock;

            $distances[$productId] = [
                'product' => $product,
                'd_plus' => round($dPlus, 4),
                'd_minus' => round($dMinus, 4),
            ];
            $preferences[$productId] = [
                'product' => $product,
                'ci' => round($preference, 4),
                'recommendation' => $isUrgent ? 'Restock Segera' : 'Pantau',
                'reason' => sprintf(
                    'Stok %d dari minimum %d; terjual %d unit/30 hari; pengadaan %d hari; margin %.1f%%.',
                    $product->stock,
                    $product->min_stock,
                    $unitsSold,
                    $product->supplier_lead_time_days,
                    $product->margin
                ),
            ];
        }

        uasort($preferences, static function (array $left, array $right): int {
            $scoreComparison = $right['ci'] <=> $left['ci'];

            return $scoreComparison !== 0
                ? $scoreComparison
                : strcasecmp($left['product']->name, $right['product']->name);
        });

        TopsisResult::query()->create([
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'generated_by' => Auth::id(),
            'criteria_snapshot' => $criteria->toArray(),
            'decision_matrix' => $matrixX,
            'normalized_matrix' => $matrixR,
            'weighted_matrix' => $matrixY,
            'ideal_positive' => $idealPositive,
            'ideal_negative' => $idealNegative,
            'distances' => $distances,
            'preferences' => $preferences,
        ]);

        return view('owner.topsis.calculation', compact(
            'criteria',
            'matrixX',
            'matrixR',
            'matrixY',
            'idealPositive',
            'idealNegative',
            'distances',
            'preferences'
        ));
    }

    public function recommendations(): View|RedirectResponse
    {
        $latestResult = TopsisResult::query()->latest()->first();
        if (! $latestResult) {
            return redirect()->route('admin.topsis.calculate');
        }

        $preferences = collect($latestResult->preferences)->map(function ($item) {
            if (! isset($item['reason'])) {
                $stock = $item['product']['stock'] ?? 0;
                $minStock = $item['product']['min_stock'] ?? 0;
                $isUrgent = $stock <= $minStock;
                $item['reason'] = $isUrgent
                    ? 'Stok berada pada atau di bawah batas minimum.'
                    : 'Evaluasi berdasarkan skor preferensi TOPSIS ('.number_format($item['ci'] ?? 0, 4).').';
            }

            return $item;
        })->all();
        $criteria = $latestResult->criteria_snapshot;

        return view('owner.topsis.recommendations', compact('preferences', 'criteria', 'latestResult'));
    }
}
