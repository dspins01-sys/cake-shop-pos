<?php

namespace App\Http\Controllers;

use App\Models\Order;

class PaymentController extends Controller
{
    public function midtrans(Order $order)
    {
        abort_unless($order->payment_method === 'midtrans', 404);

        return view('orders.midtrans', compact('order'));
    }
}
