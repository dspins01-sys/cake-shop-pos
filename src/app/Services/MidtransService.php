<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    public function createSnapToken(Order $order): string
    {
        $serverKey = config('payment.midtrans.server_key');
        if (!$serverKey) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY is not configured.');
        }

        $items = $order->items->map(fn ($item) => [
            'id' => (string) $item->product_id,
            'price' => (int) round($item->product_price),
            'quantity' => (int) $item->quantity,
            'name' => mb_substr($item->product_name, 0, 50),
        ])->values()->all();

        if ((int) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping - ' . ($order->courier_service ?: 'Delivery'),
            ];
        }

        // Tax is already included in order->total. Include it as a separate line
        // so Midtrans item_details add up to the exact gross amount.
        $productAndShippingTotal = $order->items->sum(fn ($item) => $item->product_price * $item->quantity) + (int) $order->shipping_cost;
        $tax = (int) $order->total - (int) $productAndShippingTotal;
        if ($tax > 0) {
            $items[] = [
                'id' => 'TAX',
                'price' => $tax,
                'quantity' => 1,
                'name' => 'Tax',
            ];
        }

        $finishUrl = rtrim(config('app.url'), '/') . '/payment/midtrans/finish/' . $order->id;

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) round($order->total),
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'shipping_address' => [
                    'address' => $order->address,
                ],
            ],
            'callbacks' => [
                'finish' => $finishUrl,
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post(config('payment.midtrans.snap_url'), $payload);

        if ($response->failed() || !$response->json('token')) {
            throw new RuntimeException('Midtrans error: ' . $response->body());
        }

        return $response->json('token');
    }

    public function isValidNotification(array $notification): bool
    {
        $serverKey = config('payment.midtrans.server_key');
        $signature = hash('sha512',
            ($notification['order_id'] ?? '') .
            ($notification['status_code'] ?? '') .
            ($notification['gross_amount'] ?? '') .
            $serverKey
        );

        return hash_equals($signature, $notification['signature_key'] ?? '');
    }

    public function mapPaymentStatus(array $notification): string
    {
        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? null;

        if ($transactionStatus === 'settlement') {
            return 'paid';
        }

        if ($transactionStatus === 'capture' && $fraudStatus !== 'deny') {
            return 'paid';
        }

        if (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            return 'failed';
        }

        return 'pending';
    }
}
