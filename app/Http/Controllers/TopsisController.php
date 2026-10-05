<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\TopsisCriterion;
use App\Models\TopsisResult;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class TopsisController extends Controller
{
    public function criteria()
    {
        $criteria = TopsisCriterion::orderBy('code')->get();
        $totalWeight = $criteria->sum('weight');

        return view('admin.topsis.criteria', compact('criteria', 'totalWeight'));
    }

    public function updateCriteria(Request $request)
    {
        $weights = $request->input('weights', []);
        $total = array_sum($weights);

        // Validasi toleransi pembulatan float 0.999 - 1.001
        if (abs($total - 1.00) > 0.01) {
            return redirect()->back()->with('error', "Total bobot kriteria harus tepat bernilai 1.00 (saat ini: {$total}).");
        }

        foreach ($weights as $id => $weight) {
            TopsisCriterion::where('id', $id)->update(['weight' => $weight]);
        }

        return redirect()->route('admin.topsis.criteria')->with('success', 'Bobot kriteria TOPSIS berhasil diperbarui!');
    }

    public function calculate()
    {
        $criteria = TopsisCriterion::where('is_active', true)->orderBy('code')->get();
        $products = Product::where('is_active', true)->with('category')->get();

        if ($products->count() < 2) {
            return redirect()->route('admin.products')->with('error', 'Dibutuhkan minimal 2 produk aktif untuk melakukan analisis TOPSIS.');
        }

        // 1. Bangun Matriks Keputusan (X)
        $matrixX = [];
        foreach ($products as $p) {
            // C1: Total Kuantitas Terjual
            $c1 = OrderItem::where('product_id', $p->id)->sum('qty') ?: 1;

            // C2: Dead Stock (Rasio sisa stok berbanding penjualan atau stok berlebih)
            $c2 = $p->stock > 0 ? round($p->stock / max(1, $c1), 2) : 0.5;

            // C3: Prioritas Restock (Min Stock / Current Stock)
            $c3 = round($p->min_stock / max(1, $p->stock), 3);

            // C4: Loyalitas Pelanggan (Jumlah order berulang)
            $c4 = OrderItem::where('product_id', $p->id)
                ->whereHas('order', function($q) { $q->whereNotNull('customer_id'); })
                ->count() ?: 1;

            // C5: Margin Keuntungan ((Sell - Cost) / Sell)
            $c5 = $p->sell_price > 0 ? round(($p->sell_price - $p->cost_price) / $p->sell_price, 3) : 0.1;

            // C6: Komplain / Retur (Review negatif atau retur)
            $c6 = Review::where('product_id', $p->id)->where('is_complaint', true)->count() ?: 0.1;

            $matrixX[$p->id] = [
                'product' => $p,
                'C1' => (float) $c1,
                'C2' => (float) $c2,
                'C3' => (float) $c3,
                'C4' => (float) $c4,
                'C5' => (float) $c5,
                'C6' => (float) $c6,
            ];
        }

        // 2. Normalisasi Matriks (R) : r_ij = x_ij / sqrt(sum(x_kj^2))
        $divisors = [];
        foreach ($criteria as $crit) {
            $code = $crit->code;
            $sumSq = 0;
            foreach ($matrixX as $row) {
                $sumSq += pow($row[$code], 2);
            }
            $divisors[$code] = sqrt($sumSq) ?: 1;
        }

        $matrixR = [];
        foreach ($matrixX as $pid => $row) {
            $matrixR[$pid] = ['product' => $row['product']];
            foreach ($criteria as $crit) {
                $code = $crit->code;
                $matrixR[$pid][$code] = round($row[$code] / $divisors[$code], 4);
            }
        }

        // 3. Matriks Terbobot (Y) : y_ij = w_j * r_ij
        $matrixY = [];
        foreach ($matrixR as $pid => $row) {
            $matrixY[$pid] = ['product' => $row['product']];
            foreach ($criteria as $crit) {
                $code = $crit->code;
                $matrixY[$pid][$code] = round($row[$code] * (float) $crit->weight, 4);
            }
        }

        // 4. Solusi Ideal Positif (A+) & Negatif (A-)
        $idealPositive = [];
        $idealNegative = [];

        foreach ($criteria as $crit) {
            $code = $crit->code;
            $columnValues = array_column($matrixY, $code);

            if ($crit->type === 'benefit') {
                $idealPositive[$code] = max($columnValues);
                $idealNegative[$code] = min($columnValues);
            } else {
                // Cost
                $idealPositive[$code] = min($columnValues);
                $idealNegative[$code] = max($columnValues);
            }
        }

        // 5. Jarak Euclidean D+ dan D-
        $distances = [];
        $preferences = [];

        foreach ($matrixY as $pid => $row) {
            $dPlusSum = 0;
            $dMinusSum = 0;

            foreach ($criteria as $crit) {
                $code = $crit->code;
                $dPlusSum += pow($row[$code] - $idealPositive[$code], 2);
                $dMinusSum += pow($row[$code] - $idealNegative[$code], 2);
            }

            $dPlus = round(sqrt($dPlusSum), 4);
            $dMinus = round(sqrt($dMinusSum), 4);

            // 6. Nilai Preferensi Ci = D- / (D+ + D-)
            $ci = ($dPlus + $dMinus) > 0 ? round($dMinus / ($dPlus + $dMinus), 4) : 0;

            $distances[$pid] = [
                'product' => $row['product'],
                'd_plus' => $dPlus,
                'd_minus' => $dMinus,
            ];

            // Tentukan label rekomendasi actionable
            $p = $row['product'];
            $recommendationTag = 'Normal';
            if ($p->stock <= $p->min_stock) {
                $recommendationTag = 'Restock Segera';
            } elseif ($ci >= 0.55 && $p->margin >= 15) {
                $recommendationTag = 'Produk Unggulan';
            } elseif ($row['C2'] > 2) {
                $recommendationTag = 'Promo Clearance';
            }

            $preferences[$pid] = [
                'product' => $p,
                'ci' => $ci,
                'recommendation' => $recommendationTag,
            ];
        }

        // Urutkan ranking berdasarkan Ci tertinggi
        uasort($preferences, function($a, $b) {
            return $b['ci'] <=> $a['ci'];
        });

        // Simpan Snapshot ke tabel topsis_results
        TopsisResult::create([
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'generated_by' => Auth::id() ?? 1,
            'criteria_snapshot' => $criteria->toArray(),
            'decision_matrix' => $matrixX,
            'normalized_matrix' => $matrixR,
            'weighted_matrix' => $matrixY,
            'ideal_positive' => $idealPositive,
            'ideal_negative' => $idealNegative,
            'distances' => $distances,
            'preferences' => $preferences,
        ]);

        return view('admin.topsis.calculation', compact(
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

    public function recommendations()
    {
        $latestResult = TopsisResult::latest()->first();

        // Jika belum ada riwayat kalkulasi, jalankan otomatis
        if (!$latestResult) {
            return redirect()->route('admin.topsis.calculate');
        }

        $preferences = $latestResult->preferences;
        $criteria = $latestResult->criteria_snapshot;

        return view('admin.topsis.recommendations', compact('preferences', 'criteria', 'latestResult'));
    }
}
