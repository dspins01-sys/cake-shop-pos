<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders (for admin)
     */
    public function index()
    {
        $orders = Order::with('items')->latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order (for admin)
     */
    public function show(Order $order)
    {
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show order success page (for customer after checkout)
     */
    public function success(Order $order)
    {
        return view('orders.success', compact('order'));
    }

    /**
     * Upload payment proof (for customer)
     */
    /**
 * Upload payment proof (for customer)
 */
public function uploadProof(Request $request, Order $order)
{
    $request->validate([
        'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
    ]);

    // Cek apakah order ini milik customer yang sama
    // Untuk guest, kita pake email dari session atau input
    $userEmail = auth()->check() ? auth()->user()->email : $request->email;
    
    if (!$userEmail || $order->customer_email !== $userEmail) {
        return back()->with('error', 'Unauthorized: This order does not belong to you.');
    }

    // Upload file
    if ($request->hasFile('payment_proof')) {
        $path = $request->file('payment_proof')->store('payment-proofs', 'public');
        
        $order->update([
            'payment_proof' => $path,
            'payment_status' => 'waiting_confirmation',
            'status' => 'waiting_confirmation'
        ]);
    }

    return redirect()->route('order.success', $order)
        ->with('success', 'Payment proof uploaded successfully! Your order is now waiting for admin confirmation.');
}

    /**
     * Track order by order number and email (for guest)
     */
    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'email' => 'required|email'
        ]);

        $order = Order::where('order_number', $request->order_number)
                      ->where('customer_email', $request->email)
                      ->first();

        if (!$order) {
            return back()->with('error', 'Order not found. Please check your order number and email.');
        }

        return view('orders.track', compact('order'));
    }

    /**
     * Show track order form
     */
    public function trackForm()
    {
        return view('orders.track-form');
    }

    // ============ ADMIN METHODS ============

    /**
     * Confirm payment (admin only)
     */
    public function confirmPayment(Order $order)
    {
        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'paid_at' => now()
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Payment confirmed! Order is now processing.');
    }

    /**
     * Mark order as completed (admin only)
     */
    public function complete(Order $order)
    {
        $order->update([
            'status' => 'completed'
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order completed successfully!');
    }

    /**
     * Cancel order (admin only)
     */
    public function cancel(Order $order)
    {
        $order->update([
            'status' => 'cancelled'
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order cancelled.');
    }
}