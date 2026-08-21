<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class MidtransController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

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

    public function notification(Request $request): JsonResponse
    {
        $notification = $request->all();
        if (!$this->midtrans->isValidNotification($notification)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = Order::where('order_number', $notification['order_id'] ?? '')->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        DB::transaction(function () use ($order, $notification) {
            $paymentStatus = $this->midtrans->mapPaymentStatus($notification);
            $transactionStatus = $notification['transaction_status'] ?? null;

            $updates = [
                'payment_status' => $paymentStatus,
                'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                'midtrans_payment_type' => $notification['payment_type'] ?? null,
                'midtrans_status' => $transactionStatus,
            ];

            if ($paymentStatus === 'paid') {
                $updates['paid_at'] = $order->paid_at ?: now();
                $updates['midtrans_paid_at'] = $order->midtrans_paid_at ?: now();
                $updates['status'] = 'processing';
            } elseif ($paymentStatus === 'failed') {
                $updates['status'] = 'cancelled';
            }

            $order->update($updates);
        });

        return response()->json(['message' => 'OK']);
    }
}
