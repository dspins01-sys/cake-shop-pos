<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Product;

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
   
    /**
     * Mark order as completed (admin only)
     */

    /**
     * Cancel order (admin only)
     */
   /**
 * Cancel order (admin only) - KEMBALIKAN STOK
 */
public function cancel(Order $order)
{
    // Kembalikan stok (kalo statusnya paid/processing)
    if (in_array($order->payment_status, ['paid', 'waiting_confirmation'])) {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->update([
                    'stock' => $product->stock + $item->quantity
                ]);
                \Log::info("Stok dikembalikan: {$product->name} +{$item->quantity}");
            }
        }
    }

    $order->update([
        'status' => 'cancelled'
    ]);

    return redirect()->route('admin.orders.show', $order)
        ->with('success', 'Order cancelled. Stok telah dikembalikan.');
}
    /**
 * Process order (admin only)
 */
public function processOrder(Order $order)
{
    $order->update([
        'status' => 'processing'
    ]);

    // Optional: Kirim email ke customer bahwa order sedang diproses
    // Mail::to($order->customer_email)->send(new OrderProcessing($order));

    return redirect()->route('admin.orders.show', $order)
        ->with('success', 'Order is now being processed.');
}
   public function complete(Order $order)
{
    $order->update([
        'status' => 'completed'
    ]);

    // Optional: Kirim email ke customer bahwa order selesai
    // Mail::to($order->customer_email)->send(new OrderCompleted($order));

    return redirect()->route('admin.orders.show', $order)
        ->with('success', 'Order completed successfully!');
}

/**
 * Confirm payment (admin only)
 */
/**
 * Confirm payment (admin only)
 */
public function confirmPayment(Order $order)
{
    // ============ VALIDASI STOK DULU ============
    foreach ($order->items as $item) {
        $product = Product::find($item->product_id);
        
        // Cek apakah produk masih ada
        if (!$product) {
            return back()->with('error', "Produk {$item->product_name} tidak ditemukan di database!");
        }
        
        // Cek apakah stok cukup
        if ($product->stock < $item->quantity) {
            return back()->with('error', 
                "Stok {$product->name} tidak cukup! " .
                "Dipesan: {$item->quantity}, " .
                "Sisa stok: {$product->stock}"
            );
        }
    }

    // ============ KALO SEMUA AMAN, BARU KURANGI STOK ============
    foreach ($order->items as $item) {
        $product = Product::find($item->product_id);
        $product->decrement('stock', $item->quantity);
        
        // Optional: catat log
        \Log::info("Stok berkurang: {$product->name} -{$item->quantity} (Sisa: {$product->stock})");
    }

    // Update status order
    $order->update([
        'payment_status' => 'paid',
        'status' => 'processing',
        'paid_at' => now()
    ]);

    return redirect()->route('admin.orders.show', $order)
        ->with('success', 'Payment confirmed! Stok telah diperbarui.');
}
/**
 * Mark order as completed (admin only)
 */

}