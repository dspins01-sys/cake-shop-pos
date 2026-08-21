<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransService;
use App\Services\NodeWhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MidtransController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private NodeWhatsAppService $whatsapp,
    ) {}

    public function token(Order $order): JsonResponse
    {
        if ($order->payment_method !== 'midtrans') {
            return response()->json(['message' => 'Order is not configured for Midtrans.'], 422);
        }

        try {
            $order->loadMissing('items');
            $token = $this->midtrans->createSnapToken($order);
            $order->update(['midtrans_token' => $token]);

            return response()->json(['token' => $token, 'client_key' => config('payment.midtrans.client_key')]);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unable to create payment session.'], 422);
        }
    }

    public function status(Order $order): JsonResponse
    {
        abort_unless($order->payment_method === 'midtrans', 404);

        return response()->json([
            'order_id' => $order->order_number,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'midtrans_status' => $order->midtrans_status,
            'paid_at' => $order->paid_at?->toISOString(),
        ]);
    }

    public function finish(Order $order)
    {
        abort_unless($order->payment_method === 'midtrans', 404);
        return view('orders.midtrans-finish', compact('order'));
    }

    public function notification(Request $request): JsonResponse
    {
        $notification = $request->all();
        if (!$this->midtrans->isValidNotification($notification)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = Order::with('items')->where('order_number', $notification['order_id'] ?? '')->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ((int) round($order->total) !== (int) round((float) ($notification['gross_amount'] ?? 0))) {
            return response()->json(['message' => 'Gross amount mismatch.'], 422);
        }

        $sendPaymentWhatsapp = false;

        DB::transaction(function () use ($order, $notification, &$sendPaymentWhatsapp) {
            $paymentStatus = $this->midtrans->mapPaymentStatus($notification);
            $wasAlreadyPaid = $order->payment_status === 'paid';

            $updates = [
                'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                'midtrans_payment_type' => $notification['payment_type'] ?? null,
                'midtrans_status' => $notification['transaction_status'] ?? null,
            ];

            if (!$wasAlreadyPaid) {
                $updates['payment_status'] = $paymentStatus;

                if ($paymentStatus === 'paid') {
                    $updates['paid_at'] = $order->paid_at ?: now();
                    $updates['midtrans_paid_at'] = $order->midtrans_paid_at ?: now();
                    $updates['status'] = 'processing';
                    $sendPaymentWhatsapp = empty($order->wa_payment_notified_at);

                    foreach ($order->items as $item) {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if (!$product || $product->stock < $item->quantity) {
                            throw new \RuntimeException("Stock unavailable for product #{$item->product_id}");
                        }
                        $product->decrement('stock', $item->quantity);
                    }
                } elseif ($paymentStatus === 'failed') {
                    $updates['status'] = 'cancelled';
                }
            }

            if ($sendPaymentWhatsapp) {
                $updates['wa_payment_notified_at'] = now();
            }

            $order->update($updates);
        });

        if ($sendPaymentWhatsapp) {
            $this->sendPaymentSuccessWhatsapp($order->fresh());
        }

        return response()->json(['message' => 'OK']);
    }

    private function sendPaymentSuccessWhatsapp(Order $order): void
    {
        try {
            $customerMessage = "✅ *PEMBAYARAN BERHASIL*\n\n";
            $customerMessage .= "Halo *{$order->customer_name}*,\n";
            $customerMessage .= "Pembayaran pesanan Anda telah kami terima melalui Midtrans.\n\n";
            $customerMessage .= "📋 *DETAIL PESANAN*\n";
            $customerMessage .= "No. Order: {$order->order_number}\n";
            $customerMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
            $customerMessage .= "Status: *SEDANG DIPROSES*\n\n";
            $customerMessage .= "Pesanan sedang kami siapkan. Terima kasih! 🍰";

            $this->whatsapp->send($order->customer_phone, $customerMessage);

            $adminPhone = env('ADMIN_WHATSAPP');
            if ($adminPhone) {
                $adminMessage = "💰 *PEMBAYARAN MIDTRANS BERHASIL*\n\n";
                $adminMessage .= "No. Order: {$order->order_number}\n";
                $adminMessage .= "Customer: {$order->customer_name}\n";
                $adminMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
                $adminMessage .= "Status: *PAID / PROCESSING*\n\n";
                $adminMessage .= "🔗 " . route('admin.orders.show', $order);
                $this->whatsapp->send($adminPhone, $adminMessage);
            }

            Log::info('Midtrans payment WA sent', ['order' => $order->id]);
        } catch (Throwable $e) {
            Log::error('Midtrans payment WA failed', [
                'order' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
