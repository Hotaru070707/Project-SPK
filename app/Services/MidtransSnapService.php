<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class MidtransSnapService
{
    public function createTransaction(
        Order $order,
        string $recipientName,
        string $phone
    ): array {
        $serverKey = $this->serverKey();

        $snapUrl = config('services.midtrans.snap_url');

        if (! is_string($snapUrl) || $snapUrl === '') {
            throw new RuntimeException(
                'MIDTRANS_SNAP_URL belum dikonfigurasi.'
            );
        }

        $httpClient = Http::baseUrl($snapUrl)
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($serverKey, '')
            ->connectTimeout(5)
            ->timeout(30);

        if (! config('services.midtrans.curl_verify', true)) {
            $httpClient = $httpClient->withoutVerifying();
        }

        $response = $httpClient->post('/snap/v1/transactions', [
            'transaction_details' => [
                'order_id' => $order->midtrans_order_id,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $recipientName,
                'phone' => $phone,
            ],
            'enabled_payments' => ['other_qris'],
            'callbacks' => [
                'finish' => route(
                    'orders.show',
                    [
                        'orderNumber' => $order->order_number,
                        'midtrans_return' => 1,
                    ]
                ),
            ],
        ]);

        // Catat respons error untuk mempermudah pemeriksaan.
        if (! $response->successful()) {
            logger()->error('Midtrans Snap gagal membuat transaksi', [
                'http_status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
                'order_id' => $order->midtrans_order_id,
            ]);

            throw new RuntimeException(
                'Midtrans HTTP '.$response->status()
                .': '.$response->body()
            );
        }

        $data = $response->json();

        if (
            ! is_array($data)
            || empty($data['token'])
            || empty($data['redirect_url'])
        ) {
            logger()->error(
                'Respons transaksi Midtrans tidak valid',
                [
                    'response' => $data,
                    'order_id' => $order->midtrans_order_id,
                ]
            );

            throw new RuntimeException(
                'Midtrans tidak mengembalikan token '
                .'dan URL pembayaran yang valid.'
            );
        }

        return $data;
    }

    public function syncOrderStatus(Order $order): bool
    {
        if (
            $order->payment_method !== 'midtrans_qris'
            || ! is_string($order->midtrans_order_id)
            || $order->midtrans_order_id === ''
        ) {
            return false;
        }

        $serverKey = $this->serverKey();
        $apiUrl = config('services.midtrans.api_url');

        if (! is_string($apiUrl) || $apiUrl === '') {
            throw new RuntimeException(
                'MIDTRANS_API_URL belum dikonfigurasi.'
            );
        }

        try {
            $httpClient = Http::baseUrl($apiUrl)
                ->acceptJson()
                ->withBasicAuth($serverKey, '')
                ->connectTimeout(5)
                ->timeout(10);

            if (! config('services.midtrans.curl_verify', true)) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get(
                '/v2/'
                .rawurlencode($order->midtrans_order_id)
                .'/status'
            );
        } catch (ConnectionException $exception) {
            logger()->warning(
                'Gagal memeriksa status pembayaran Midtrans',
                [
                    'order_id' => $order->midtrans_order_id,
                    'exception' => $exception->getMessage(),
                ]
            );

            return false;
        }

        $notification = $response->json();

        if (! $response->successful() || ! is_array($notification)) {
            logger()->warning(
                'Respons pemeriksaan status Midtrans tidak valid',
                [
                    'http_status' => $response->status(),
                    'order_id' => $order->midtrans_order_id,
                ]
            );

            return false;
        }

        try {
            return $this->handleNotification($notification);
        } catch (InvalidArgumentException $exception) {
            logger()->warning(
                'Signature respons status Midtrans tidak valid',
                [
                    'order_id' => $order->midtrans_order_id,
                    'exception' => $exception->getMessage(),
                ]
            );

            return false;
        }
    }

    public function handleNotification(array $notification): bool
    {
        $serverKey = $this->serverKey();

        $signature = hash(
            'sha512',
            ($notification['order_id'] ?? '')
            .($notification['status_code'] ?? '')
            .($notification['gross_amount'] ?? '')
            .$serverKey
        );

        if (
            ! isset($notification['signature_key'])
            || ! hash_equals(
                $signature,
                (string) $notification['signature_key']
            )
        ) {
            throw new InvalidArgumentException(
                'Signature not valid.'
            );
        }

        $order = Order::query()
            ->where(
                'midtrans_order_id',
                $notification['order_id']
            )
            ->where('payment_method', 'midtrans_qris')
            ->first();

        if (
            $order === null
            || ! isset($notification['gross_amount'])
            || ! hash_equals(
                number_format((float) $order->total, 2, '.', ''),
                (string) $notification['gross_amount']
            )
        ) {
            return false;
        }

        $statusCode = (string) (
            $notification['status_code'] ?? ''
        );

        $transactionStatus = strtolower(
            (string) ($notification['transaction_status'] ?? '')
        );

        $attributes = match ($transactionStatus) {
            'settlement' => $statusCode === '200' ? [
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'paid_at' => now(),
            ] : null,

            'capture' => $statusCode === '200'
                && strtolower(
                    (string) ($notification['fraud_status'] ?? '')
                ) === 'accept'
                ? [
                    'payment_status' => 'paid',
                    'order_status' => 'processing',
                    'paid_at' => now(),
                ]
                : null,

            'pending' => [
                'payment_status' => 'pending',
            ],

            'expire' => [
                'payment_status' => 'expired',
            ],

            default => null,
        };

        if ($attributes !== null) {
            Order::query()
                ->whereKey($order->id)
                ->where('payment_status', '!=', 'paid')
                ->update($attributes);
        }

        return true;
    }

    private function serverKey(): string
    {
        $serverKey = config('services.midtrans.server_key');

        if (! is_string($serverKey) || $serverKey === '') {
            throw new RuntimeException(
                'MIDTRANS_SERVER_KEY belum dikonfigurasi.'
            );
        }

        $isProduction = (bool) config(
            'services.midtrans.is_production'
        );

        $expectedPrefix = $isProduction
            ? 'Mid-server-'
            : 'SB-Mid-server-';

        if (! str_starts_with($serverKey, $expectedPrefix)) {
            $environment = $isProduction
                ? 'Production'
                : 'Sandbox';

            throw new RuntimeException(
                "Mode {$environment} memerlukan Server Key "
                ."{$expectedPrefix}."
            );
        }

        return $serverKey;
    }
}
