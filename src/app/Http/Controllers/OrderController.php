<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\NodeWhatsAppService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $whatsapp;
     public function __construct(NodeWhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }
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
     /**
     * Order success (setelah checkout)
     */
    public function success(Order $order)
    {
        // 1. WA KE CUSTOMER - Order Diterima
        $customerMessage = "🍰 *SweetCake Bakery*\n\n";
        $customerMessage .= "Halo *{$order->customer_name}*,\n";
        $customerMessage .= "Terima kasih telah order di SweetCake!\n\n";
        $customerMessage .= "📋 *DETAIL PESANAN*\n";
        $customerMessage .= "No. Order: {$order->order_number}\n";
        $customerMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
        $customerMessage .= "Status: Menunggu Pembayaran\n\n";
        $customerMessage .= "💳 *INSTRUKSI PEMBAYARAN*\n";
        $customerMessage .= "Transfer ke:\n";
        $customerMessage .= "🏦 BCA: 1234567890 a.n. SweetCake\n";
        $customerMessage .= "🏦 Mandiri: 9876543210 a.n. SweetCake\n\n";
        $customerMessage .= "📤 *UPLOAD BUKTI*\n";
        $customerMessage .= "Upload bukti transfer di:\n";
        $customerMessage .= route('order.short', $order) . "\n\n";
        $customerMessage .= "Konfirmasi pembayaran maks 1x24 jam.\n";
        $customerMessage .= "Terima kasih! 🙏";

        $this->whatsapp->send($order->customer_phone, $customerMessage);

        // 2. WA KE ADMIN - Notifikasi Order Baru
        $adminMessage = "🆕 *ORDER BARU MASUK!*\n\n";
        $adminMessage .= "📋 *Detail Order:*\n";
        $adminMessage .= "No. Order: {$order->order_number}\n";
        $adminMessage .= "Customer: {$order->customer_name}\n";
        $adminMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
        $adminMessage .= "Status: Menunggu Pembayaran\n\n";
        $adminMessage .= "🔗 Link: " . route('admin.orders.show', $order);

        $this->whatsapp->send(env('ADMIN_WHATSAPP'), $adminMessage);

        return view('orders.success', compact('order'));
    }

    /**
     * Upload payment proof
     */
    public function uploadProof(Request $request, Order $order)
    {
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');
            
            $order->update([
                'payment_proof' => $path,
                'payment_status' => 'waiting_confirmation',
                'status' => 'waiting_confirmation'
            ]);

            // 3. WA KE ADMIN - Bukti Transfer Upload
            $adminMessage = "📎 *BUKTI TRANSFER UPLOAD*\n\n";
            $adminMessage .= "No. Order: {$order->order_number}\n";
            $adminMessage .= "Customer: {$order->customer_name}\n";
            $adminMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n\n";
            $adminMessage .= "🔗 Lihat Bukti:\n";
            $adminMessage .= asset('storage/' . $path) . "\n\n";
            $adminMessage .= "🔗 Proses Order:\n" . route('admin.orders.show', $order);

            $this->whatsapp->send(env('ADMIN_WHATSAPP'), $adminMessage);
        }

        return redirect()->route('order.success', $order)
            ->with('success', 'Bukti transfer berhasil diupload!');
    }

    /**
     * Confirm payment (admin)
     */
    public function confirmPayment(Order $order)
    {
        // Validasi stok
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product->stock < $item->quantity) {
                return back()->with('error', "Stok {$product->name} tidak cukup!");
            }
        }

        // Kurangi stok
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            $product->decrement('stock', $item->quantity);
        }

        // Update status
        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'paid_at' => now()
        ]);

        // 4. WA KE CUSTOMER - Pembayaran Dikonfirmasi
        $customerMessage = "✅ *PEMBAYARAN DIKONFIRMASI*\n\n";
        $customerMessage .= "Halo *{$order->customer_name}*,\n";
        $customerMessage .= "Pembayaran Anda telah kami terima dan konfirmasi!\n\n";
        $customerMessage .= "📋 *DETAIL PESANAN*\n";
        $customerMessage .= "No. Order: {$order->order_number}\n";
        $customerMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
        $customerMessage .= "Status: *SEDANG DIPROSES*\n\n";
        $customerMessage .= "Pesanan sedang kami siapkan.\n";
        $customerMessage .= "Kami kabari lagi ya! 🍰";

        $this->whatsapp->send($order->customer_phone, $customerMessage);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Payment confirmed! Stok telah diperbarui.');
    }

    /**
     * Process order (admin)
     */
    public function processOrder(Order $order)
    {
        $order->update([
            'status' => 'processing'
        ]);

        // 5. WA KE CUSTOMER - Pesanan Diproses
        $customerMessage = "⚙️ *PESANAN DIPROSES*\n\n";
        $customerMessage .= "Halo *{$order->customer_name}*,\n";
        $customerMessage .= "Pesanan Anda sedang kami proses!\n\n";
        $customerMessage .= "📋 *DETAIL PESANAN*\n";
        $customerMessage .= "No. Order: {$order->order_number}\n";
        $customerMessage .= "Status: *SEDANG DIPROSES*\n\n";
        $customerMessage .= "Kami akan kabari ketika pesanan dikirim. 🚚";

        $this->whatsapp->send($order->customer_phone, $customerMessage);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order is now being processed.');
    }

    /**
     * Complete order (admin)
     */
    public function complete(Order $order)
    {
        $order->update([
            'status' => 'completed'
        ]);

        // 6. WA KE CUSTOMER - Pesanan Dikirim
        $customerMessage = "📦 *PESANAN DIKIRIM*\n\n";
        $customerMessage .= "Halo *{$order->customer_name}*,\n";
        $customerMessage .= "Pesanan Anda sudah kami kirim!\n\n";
        $customerMessage .= "📋 *DETAIL PESANAN*\n";
        $customerMessage .= "No. Order: {$order->order_number}\n";
        $customerMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
        $customerMessage .= "Status: *SELESAI*\n\n";
        $customerMessage .= "Terima kasih telah berbelanja di SweetCake!\n";
        $customerMessage .= "Jangan lupa kasih review ya! ⭐⭐⭐⭐⭐\n\n";
        $customerMessage .= "SweetCake Bakery 🍰";

        $this->whatsapp->send($order->customer_phone, $customerMessage);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order completed!');
    }

    /**
     * Cancel order (admin)
     */
    public function cancel(Order $order)
    {
        // Kembalikan stok kalo sudah paid
        if (in_array($order->payment_status, ['paid', 'waiting_confirmation'])) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        }

        $order->update([
            'status' => 'cancelled'
        ]);

        // 7. WA KE CUSTOMER - Pesanan Dibatalkan
        $customerMessage = "❌ *PESANAN DIBATALKAN*\n\n";
        $customerMessage .= "Halo *{$order->customer_name}*,\n";
        $customerMessage .= "Pesanan Anda telah dibatalkan.\n\n";
        $customerMessage .= "📋 *DETAIL PESANAN*\n";
        $customerMessage .= "No. Order: {$order->order_number}\n";
        $customerMessage .= "Status: *DIBATALKAN*\n\n";
        $customerMessage .= "Jika ada pertanyaan, silakan hubungi kami.\n";
        $customerMessage .= "Terima kasih! 🙏";

        $this->whatsapp->send($order->customer_phone, $customerMessage);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order cancelled.');
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


}