<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Helpers\CartHelper;
use Illuminate\Http\Request;
use App\Models\Order;

class CartController extends Controller
{
    /**
     * Display cart page
     */
    public function index()
    {
        $cart = CartHelper::getCart();
        $total = CartHelper::getTotal();
        
        return view('cart.index', compact('cart', 'total'));
    }

    /**
     * Add item to cart
     */
   // Di CartController@add
public function add(Request $request, Product $product)
{
    $availableStock = $product->available_stock; // pake accessor
    
    $request->validate([
        'quantity' => 'required|integer|min:1|max:' . $availableStock
    ]);
    
    CartHelper::addToCart($product, $request->quantity);
    return redirect()->back()->with('success', 'Product added to cart!');
}

    /**
     * Update cart item
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        CartHelper::updateCart($id, $request->quantity);

        return redirect()->route('cart.index')->with('success', 'Cart updated!');
    }

    /**
     * Remove item from cart
     */
    public function remove($id)
    {
        CartHelper::removeFromCart($id);
        
        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        CartHelper::clearCart();
        
        return redirect()->route('cart.index')->with('success', 'Cart cleared!');
    }

    /**
     * Show checkout page
     */
    /**
 * Show checkout page
 */
/**
 * Show checkout page
 */
public function checkout()
{
    $cart = CartHelper::getCart();
    
    if (empty($cart)) {
        return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
    }
    
    // VALIDASI STOK SEBELUM CHECKOUT
    $stockIssues = [];
    $cartUpdated = false;
    
    foreach ($cart as $id => $item) {
        $product = Product::find($id);
        
        if (!$product) {
            // Produk sudah tidak ada - hapus
            CartHelper::removeFromCart($id);
            $stockIssues[] = "{$item['name']} (produk tidak tersedia)";
            $cartUpdated = true;
        } 
        elseif ($product->stock < $item['quantity']) {
            // Stok tidak cukup - catat issue tapi jangan hapus dulu
            $stockIssues[] = [
                'id' => $id,
                'name' => $item['name'],
                'requested' => $item['quantity'],
                'available' => $product->stock,
                'max' => $product->stock
            ];
        }
    }
    
    // Kalo ada issue stok, redirect ke cart dengan pesan
    if (!empty($stockIssues)) {
        $message = 'Beberapa item melebihi stok tersedia:';
        foreach ($stockIssues as $issue) {
            if (is_array($issue)) {
                $message .= " • {$issue['name']}: kamu minta {$issue['requested']}, stok hanya {$issue['available']}";
            } else {
                $message .= " • {$issue}";
            }
        }
        $message .= ' Silakan sesuaikan jumlah pesanan.';
        
        return redirect()->route('cart.index')
            ->with('warning', $message)
            ->with('stock_issues', $stockIssues);
    }
    
    $total = CartHelper::getTotal();
    return view('cart.checkout', compact('cart', 'total'));
}
    /**
 * Process checkout and create order
 */
public function process(Request $request)
{
    // Validasi input
    $request->validate([
        'customer_name' => 'required|string|max:255',
        'customer_email' => 'required|email|max:255',
        'customer_phone' => 'required|string|max:20',
        'address' => 'required|string',
        'payment_method' => 'required|in:manual',
        'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'notes' => 'nullable|string'
    ]);

    // Ambil cart dari session
     $cart = CartHelper::getCart();
    
    // VALIDASI STOK FINAL SEBELUM BUAT ORDER
    foreach ($cart as $id => $item) {
        $product = Product::find($id);
        if (!$product || $product->stock < $item['quantity']) {
            return back()->with('error', 
                "Stok {$item['name']} berubah! Tersedia: " . ($product->stock ?? 0)
            )->withInput();
        }
    }
    
    if (empty($cart)) {
        return redirect()->route('cart.index')
            ->with('error', 'Your cart is empty!');
    }

    // Hitung total
    $total = CartHelper::getTotal();

   // Buat order number unik
$orderNumber = 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());

// Buat tracking code UNIK (WAJIB ADA!)
$trackingCode = 'TRK-' . date('Ymd') . '-' . strtoupper(uniqid());

// Simpan ke database
$order = \App\Models\Order::create([
    'order_number' => $orderNumber,
    'tracking_code' => $trackingCode, // <-- INI WAJIB ADA!
    'customer_name' => $request->customer_name,
    'customer_email' => $request->customer_email,
    'customer_phone' => $request->customer_phone,
    'address' => $request->address,
    'total' => $total,
    'status' => 'pending',
    'payment_method' => $request->payment_method,
    'payment_status' => 'unpaid',
    'notes' => $request->notes,
]);

    // Simpan order items
    foreach ($cart as $id => $item) {
        $order->items()->create([
            'product_id' => $id,
            'product_name' => $item['name'],
            'product_price' => $item['price'],
            'quantity' => $item['quantity'],
            'subtotal' => $item['price'] * $item['quantity'],
        ]);
    }

    // Upload bukti transfer kalo ada
    if ($request->hasFile('payment_proof')) {
        $path = $request->file('payment_proof')->store('payment-proofs', 'public');
        $order->update(['payment_proof' => $path]);
    }

    // Hapus cart dari session
    CartHelper::clearCart();

    // Redirect ke halaman sukses (bikin dulu nanti)
    return redirect()->route('order.success', $order)
        ->with('success', 'Order placed successfully! Check your email for confirmation.');
}

}
