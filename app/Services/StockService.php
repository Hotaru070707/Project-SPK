<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class StockService
{
    /**
     * Set the current stock and record the corresponding audit movement.
     *
     * @param  'in'|'out'|'adjustment'|'return'  $type
     */
    public function setStock(
        Product $product,
        int $stockAfter,
        string $type,
        ?int $userId,
        string $reason,
        ?int $orderId = null
    ): ?StockMovement {
        if (! in_array($type, ['in', 'out', 'adjustment', 'return'], true)) {
            throw new InvalidArgumentException('Jenis mutasi stok tidak valid.');
        }

        if ($stockAfter < 0) {
            throw ValidationException::withMessages([
                'stock' => ['Stok tidak boleh bernilai negatif.'],
            ]);
        }

        if (blank($reason)) {
            throw new InvalidArgumentException('Alasan mutasi stok wajib tersedia.');
        }

        return DB::transaction(function () use ($product, $stockAfter, $type, $userId, $reason, $orderId): ?StockMovement {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $stockBefore = $lockedProduct->stock;
            $difference = $stockAfter - $stockBefore;

            if ($difference === 0) {
                return null;
            }

            if (($type === 'in' || $type === 'return') && $difference < 0) {
                throw new InvalidArgumentException('Mutasi masuk harus menambah stok.');
            }

            if ($type === 'out' && $difference > 0) {
                throw new InvalidArgumentException('Mutasi keluar harus mengurangi stok.');
            }

            $lockedProduct->update(['stock' => $stockAfter]);

            return StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'order_id' => $orderId,
                'type' => $type,
                'qty' => $type === 'adjustment' ? $difference : abs($difference),
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
            ]);
        });
    }

    public function restoreUnpaidOrderStock(Order $order, ?int $userId, string $reason): void
    {
        DB::transaction(function () use ($order, $userId, $reason): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->type !== 'online' || $lockedOrder->payment_status === 'paid') {
                return;
            }

            $outgoingMovements = StockMovement::query()
                ->where('order_id', $lockedOrder->id)
                ->where('type', 'out')
                ->get()
                ->groupBy('product_id');

            foreach ($outgoingMovements as $productId => $movements) {
                $alreadyReturned = StockMovement::query()
                    ->where('order_id', $lockedOrder->id)
                    ->where('product_id', $productId)
                    ->where('type', 'return')
                    ->exists();
                if ($alreadyReturned) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->findOrFail($productId);
                $quantity = $movements->sum('qty');
                $this->setStock(
                    $product,
                    $product->stock + $quantity,
                    'return',
                    $userId,
                    $reason,
                    $lockedOrder->id
                );
            }
        });
    }
}
