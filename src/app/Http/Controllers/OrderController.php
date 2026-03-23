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
     * Tampilkan halaman upload bukti (TANPA KIRIM WA)
     */
    public function uploadPage(Order $order)
    {
        return view('orders.success', compact('order'));
    }

    /**
     * Proses setelah checkout (kirim WA ke customer & admin) - SEKALI AJA!
     */
    public function checkoutSuccess(Order $order)
    {
        Log::info('checkoutSuccess dipanggil', [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'time' => now(),
        'url' => request()->url(),
        'method' => request()->method()
    ]);
        // 1. WA KE CUSTOMER - Order Diterima
        $customerMessage = "🍰 *CremenCrumb Bakery*\n\n";
        $customerMessage .= "Halo *{$order->customer_name}*,\n";
        $customerMessage .= "Terima kasih telah order di CremenCrumb!\n\n";
        $customerMessage .= "📋 *DETAIL PESANAN*\n";
        $customerMessage .= "No. Order: {$order->order_number}\n";
        $customerMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
        $customerMessage .= "Status: Menunggu Pembayaran\n\n";
        $customerMessage .= "💳 *INSTRUKSI PEMBAYARAN*\n";
        $customerMessage .= "Transfer ke:\n";
        $customerMessage .= "🏦 BCA: 1234567890 a.n. CremenCrumb\n";
        $customerMessage .= "🏦 Mandiri: 9876543210 a.n. CremenCrumb\n\n";
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

        // Redirect ke halaman upload (tanpa kirim WA lagi)
        return redirect()->route('order.upload', $order);
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
            $adminMessage .= "💰 *Status: Menunggu Konfirmasi*\n\n";
            $adminMessage .= "🔗 Lihat Bukti:\n";
            $adminMessage .= asset('storage/' . $path) . "\n\n";
            $adminMessage .= "🔗 Proses Order:\n" . route('admin.orders.show', $order);

            $this->whatsapp->send(env('ADMIN_WHATSAPP'), $adminMessage);
        }

        return redirect()->route('order.upload', $order)
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
        $customerMessage .= "Terima kasih telah berbelanja di CremenCrumb!\n";
        $customerMessage .= "Jangan lupa kasih review ya! ⭐⭐⭐⭐⭐\n\n";
        $customerMessage .= "CremenCrumb Bakery 🍰";

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

    /**
     * Delete order (admin only) - HATI-HATI!
     */
    public function destroy(Order $order)
    {
        // Cek apakah order bisa dihapus
        if ($order->payment_status == 'paid' || $order->status == 'processing' || $order->status == 'completed') {
            return back()->with('error', 'Tidak bisa menghapus order yang sudah diproses!');
        }

        // Hapus order items dulu (karena foreign key)
        $order->items()->delete();
        
        // Hapus order
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order berhasil dihapus!');
    }

    /**
     * Delete all orders (admin only) - SUPER HATI-HATI!
     */
    public function clearAll()
    {
        // Cek dulu apakah ada order yang sudah diproses
        $processedOrders = Order::whereIn('status', ['processing', 'completed'])
            ->orWhere('payment_status', 'paid')
            ->count();
        
        if ($processedOrders > 0) {
            return back()->with('error', 'Tidak bisa hapus semua! Masih ada order yang sudah diproses.');
        }

        // Hapus semua order items dulu
        \DB::table('order_items')->delete();
        
        // Hapus semua orders
        \DB::table('orders')->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Semua order berhasil dihapus!');
    }
    /**
 * Clear all orders WITH restoring stock (untuk testing/reset total)
 */
public function clearAllWithRestore()
{
    // 1. Kembalikan stok untuk order yang sudah mengurangi stok
    $orders = Order::whereIn('payment_status', ['paid', 'waiting_confirmation'])
        ->whereIn('status', ['processing', 'completed', 'paid'])
        ->get();
    
    $restoredCount = 0;
    foreach ($orders as $order) {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', $item->quantity);
                $restoredCount++;
            }
        }
    }

    // 2. Hapus semua order items
    \DB::table('order_items')->delete();
    
    // 3. Hapus semua orders
    \DB::table('orders')->delete();

    return redirect()->route('admin.orders.index')
        ->with('success', "Reset total! $restoredCount item stok dikembalikan. Semua order dihapus.");
}
}