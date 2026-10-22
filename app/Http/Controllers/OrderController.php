<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransSnapService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in([
                'pending_payment',
                'processing',
                'packed',
                'shipped',
                'completed',
                'cancelled',
                'void',
            ])],
            'search' => ['nullable', 'string', 'max:40'],
        ]);

        $orders = Order::query()
            ->where('type', 'online')
            ->where('customer_id', $request->user()->id)
            ->when(
                $validated['status'] ?? null,
                fn ($query, string $status) => $query->where('order_status', $status)
            )
            ->when(
                $validated['search'] ?? null,
                fn ($query, string $search) => $query->where('order_number', 'like', '%'.$search.'%')
            )
            ->with(['items.product'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        [$orderStatusLabels, $paymentStatusLabels] = $this->statusLabels();

        return view('customer.orders.index', compact(
            'orders',
            'orderStatusLabels',
            'paymentStatusLabels'
        ));
    }

    public function show(
        Request $request,
        string $orderNumber,
        MidtransSnapService $midtrans
    ): View {
        $order = Order::query()
            ->where('type', 'online')
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'customer', 'address'])
            ->firstOrFail();

        if ($request->user()) {
            abort_unless(
                $request->user()->isCustomer()
                    && $order->customer_id === $request->user()->id,
                404
            );
        } else {
            abort_unless($order->customer_id === null, 404);
        }

        if (
            $request->boolean('midtrans_return')
            && $order->payment_method === 'midtrans_qris'
            && $order->payment_status !== 'paid'
        ) {
            try {
                $midtrans->syncOrderStatus($order);
            } catch (RuntimeException $exception) {
                report($exception);
            }

            $order->refresh();
        }

        [$orderStatusLabels, $paymentStatusLabels] = $this->statusLabels();

        return view('customer.orders.show', compact('order', 'orderStatusLabels', 'paymentStatusLabels'));
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    private function statusLabels(): array
    {
        return [
            [
                'pending_payment' => 'Menunggu Pembayaran',
                'processing' => 'Sedang Diproses',
                'packed' => 'Siap Diambil',
                'shipped' => 'Dalam Pengiriman',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'void' => 'Dibatalkan',
            ],
            [
                'unpaid' => 'Belum Dibayar',
                'pending' => 'Menunggu Pembayaran',
                'paid' => 'Berhasil',
                'expired' => 'Kedaluwarsa',
                'refunded' => 'Dikembalikan',
            ],
        ];
    }

    public function midtransNotification(Request $request, MidtransSnapService $midtrans): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'string'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'fraud_status' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid notification payload.'], 422);
        }

        try {
            $processed = $midtrans->handleNotification($validator->validated());
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        } catch (RuntimeException) {
            return response()->json(['message' => 'Payment integration is not configured.'], 503);
        }

        if (! $processed) {
            return response()->json(['message' => 'Order or payment amount not found.'], 404);
        }

        return response()->json(['message' => 'Notification processed.']);
    }
}
