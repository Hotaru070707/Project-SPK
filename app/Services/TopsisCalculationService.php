<?php

namespace App\Services;

use InvalidArgumentException;

class TopsisCalculationService
{
    /**
     * @param  array<int, array{code: string, name: string, type: string, weight: float}>  $criteria
     * @param  array<int, array{product: array<string, mixed>, C1: float, C2: float, C3: float}>  $matrixX
     * @return array{
     *     normalized_matrix: array<int, array<string, mixed>>,
     *     weighted_matrix: array<int, array<string, mixed>>,
     *     ideal_positive: array<string, float>,
     *     ideal_negative: array<string, float>,
     *     distances: array<int, array<string, mixed>>,
     *     preferences: array<int, array<string, mixed>>,
     *     zero_divisor_codes: array<int, string>,
     *     alternatives_identical: bool
     * }
     */
    public function calculate(array $criteria, array $matrixX): array
    {
        if ($criteria === [] || $matrixX === []) {
            throw new InvalidArgumentException('Kriteria dan alternatif TOPSIS wajib tersedia.');
        }

        $criterionCodes = [];
        $weightUnits = 0;
        foreach ($criteria as $criterion) {
            if (
                ! isset($criterion['code'], $criterion['type'], $criterion['weight'])
                || ! in_array($criterion['type'], ['benefit', 'cost'], true)
                || ! is_numeric($criterion['weight'])
                || ! is_finite((float) $criterion['weight'])
                || (float) $criterion['weight'] < 0
                || (float) $criterion['weight'] > 1
                || isset($criterionCodes[$criterion['code']])
            ) {
                throw new InvalidArgumentException('Konfigurasi kriteria TOPSIS tidak valid.');
            }

            $criterionCodes[$criterion['code']] = true;
            $weightUnits += (int) round((float) $criterion['weight'] * 10000);
        }

        if ($weightUnits !== 10000) {
            throw new InvalidArgumentException('Total bobot TOPSIS harus tepat 1,00.');
        }

        foreach ($matrixX as $row) {
            if (! isset($row['product']) || ! is_array($row['product'])) {
                throw new InvalidArgumentException('Data produk alternatif TOPSIS tidak valid.');
            }

            foreach ($criteria as $criterion) {
                $value = $row[$criterion['code']] ?? null;
                if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0) {
                    throw new InvalidArgumentException('Nilai kriteria TOPSIS kosong atau tidak valid.');
                }
            }
        }

        $divisors = [];
        $zeroDivisorCodes = [];

        foreach ($criteria as $criterion) {
            $sumOfSquares = 0.0;
            foreach ($matrixX as $row) {
                $sumOfSquares += $row[$criterion['code']] ** 2;
            }

            $divisors[$criterion['code']] = sqrt($sumOfSquares);
            if ($divisors[$criterion['code']] === 0.0) {
                $zeroDivisorCodes[] = $criterion['code'];
            }
        }

        $matrixR = [];
        foreach ($matrixX as $productId => $row) {
            $matrixR[$productId] = ['product' => $row['product']];
            foreach ($criteria as $criterion) {
                $code = $criterion['code'];
                $matrixR[$productId][$code] = $divisors[$code] > 0
                    ? $row[$code] / $divisors[$code]
                    : 0.0;
            }
        }

        $matrixY = [];
        foreach ($matrixR as $productId => $row) {
            $matrixY[$productId] = ['product' => $row['product']];
            foreach ($criteria as $criterion) {
                $code = $criterion['code'];
                $matrixY[$productId][$code] = $row[$code] * $criterion['weight'];
            }
        }

        $idealPositive = [];
        $idealNegative = [];
        foreach ($criteria as $criterion) {
            $code = $criterion['code'];
            $values = array_column($matrixY, $code);

            if ($criterion['type'] === 'benefit') {
                $idealPositive[$code] = max($values);
                $idealNegative[$code] = min($values);
            } else {
                $idealPositive[$code] = min($values);
                $idealNegative[$code] = max($values);
            }
        }

        $distances = [];
        $preferences = [];
        foreach ($matrixY as $productId => $row) {
            $dPlusSquared = 0.0;
            $dMinusSquared = 0.0;

            foreach ($criteria as $criterion) {
                $code = $criterion['code'];
                $dPlusSquared += ($row[$code] - $idealPositive[$code]) ** 2;
                $dMinusSquared += ($row[$code] - $idealNegative[$code]) ** 2;
            }

            $dPlus = sqrt($dPlusSquared);
            $dMinus = sqrt($dMinusSquared);
            $distanceTotal = $dPlus + $dMinus;
            $preference = $distanceTotal > 0 ? $dMinus / $distanceTotal : 0.5;

            $distances[$productId] = [
                'product' => $row['product'],
                'd_plus' => $dPlus,
                'd_minus' => $dMinus,
            ];
            $preferences[$productId] = [
                'product' => $row['product'],
                'ci' => $preference,
            ];
        }

        uasort($preferences, static function (array $left, array $right): int {
            $scoreComparison = $right['ci'] <=> $left['ci'];

            return $scoreComparison !== 0
                ? $scoreComparison
                : strcasecmp($left['product']['name'], $right['product']['name']);
        });

        $rank = 1;
        foreach ($preferences as &$preference) {
            $preference['rank'] = $rank;
            $preference['d_plus'] = $distances[$preference['product']['id']]['d_plus'];
            $preference['d_minus'] = $distances[$preference['product']['id']]['d_minus'];
            $preference['recommendation'] = $rank === 1
                ? 'Prioritas tertinggi'
                : 'Prioritas relatif';
            $rank++;
        }
        unset($preference);

        return [
            'normalized_matrix' => $matrixR,
            'weighted_matrix' => $matrixY,
            'ideal_positive' => $idealPositive,
            'ideal_negative' => $idealNegative,
            'distances' => $distances,
            'preferences' => $preferences,
            'zero_divisor_codes' => $zeroDivisorCodes,
            'alternatives_identical' => count(array_unique(array_map(
                static fn (array $row): string => json_encode(
                    array_intersect_key($row, array_flip(['C1', 'C2', 'C3'])),
                    JSON_THROW_ON_ERROR
                ),
                $matrixX
            ))) === 1,
        ];
    }
}
