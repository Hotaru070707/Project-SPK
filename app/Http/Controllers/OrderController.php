<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransSnapService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use RuntimeException;

class OrderController extends Controller
{
    public function show(
        Request $request,
        string $orderNumber,
        MidtransSnapService $midtrans
    ): View {
        $order = Order::where('order_number', $orderNumber)->with(['items.product', 'customer'])->firstOrFail();

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

        return view('customer.orders.show', compact('order'));
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
