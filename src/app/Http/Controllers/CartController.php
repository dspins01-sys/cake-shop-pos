<?php

namespace App\Http\Controllers;

use App\Helpers\CartHelper;
use App\Models\Order;
use App\Models\Product;
use App\Services\NodeWhatsAppService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(private NodeWhatsAppService $whatsapp) {}

    public function index()
    {
        $cart = CartHelper::getCart();
        $total = CartHelper::getTotal();
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        $availableStock = $product->available_stock;
        $request->validate(['quantity' => 'required|integer|min:1|max:' . $availableStock]);
        CartHelper::addToCart($product, $request->quantity);
        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:0']);
        CartHelper::updateCart($id, $request->quantity);
        return redirect()->route('cart.index')->with('success', 'Cart updated!');
    }

    public function remove($id)
    {
        CartHelper::removeFromCart($id);
        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    public function clear()
    {
        CartHelper::clearCart();
        return redirect()->route('cart.index')->with('success', 'Cart cleared!');
    }

    public function checkout()
    {
        $cart = CartHelper::getCart();
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if (($product->available_stock ?? 0) < $item['quantity']) {
                return redirect()->route('cart.index')->with('error', "Stok {$item['name']} berubah!");
            }
        }

        $total = CartHelper::getTotal();
        $weight = $this->cartWeight($cart);
        return view('cart.checkout', compact('cart', 'total', 'weight'));
    }

    public function process(Request $request, RajaOngkirService $rajaOngkir)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string',
            'payment_method' => 'required|in:manual,midtrans',
            'payment_proof' => 'nullable|required_if:payment_method,manual|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'shipping_destination_id' => 'required|integer',
            'shipping_province' => 'required|string|max:100',
            'shipping_city' => 'required|string|max:100',
            'shipping_district' => 'required|string|max:100',
            'courier' => 'required|string|max:30',
            'courier_service' => 'required|string|max:50',
        ]);

        $cart = CartHelper::getCart();
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $weight = $this->cartWeight($cart);
        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if (!$product || $product->available_stock < $item['quantity']) {
                return back()->with('error', "Stok {$item['name']} berubah!")->withInput();
            }
        }

        $quote = $rajaOngkir->findQuote(
            (int) $data['shipping_destination_id'],
            $weight,
            $data['courier'],
            $data['courier_service']
        );

        if (!$quote) {
            return back()->with('error', 'Tarif ongkir sudah berubah. Silakan pilih layanan lagi.')->withInput();
        }

        $shippingCost = (int) ($quote['cost'] ?? 0);
        $shippingEtd = $quote['etd'] ?? null;
        $subtotal = (float) CartHelper::getTotal();
        $tax = round($subtotal * 0.10);
        $grandTotal = (int) round($subtotal + $tax + $shippingCost);

        $order = DB::transaction(function () use ($data, $cart, $grandTotal, $shippingCost, $weight, $shippingEtd) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber() . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
                'tracking_code' => 'TRK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'address' => $data['address'],
                'total' => $grandTotal,
                'shipping_cost' => $shippingCost,
                'shipping_weight' => $weight,
                'shipping_destination_id' => $data['shipping_destination_id'],
                'shipping_province' => $data['shipping_province'],
                'shipping_city' => $data['shipping_city'],
                'shipping_district' => $data['shipping_district'],
                'courier' => $data['courier'],
                'courier_service' => $data['courier_service'],
                'shipping_etd' => $shippingEtd,
                'expired_at' => now()->addHours(24),
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cart as $id => $item) {
                $order->items()->create([
                    'product_id' => $id,
                    'product_name' => $item['name'],
                    'product_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            if (!empty($data['payment_proof'])) {
                $path = request()->file('payment_proof')->store('payment-proofs', 'public');
                $order->update(['payment_proof' => $path]);
            }

            return $order;
        });

        CartHelper::clearCart();

        if ($order->payment_method === 'midtrans') {
            // Midtrans orders previously skipped checkoutSuccess(), so explicitly
            // restore the "order received / awaiting payment" WA notification here.
            try {
                $customerMessage = "🍰 *CremenCrumb Bakery*\n\n";
                $customerMessage .= "Halo *{$order->customer_name}*,\n";
                $customerMessage .= "Terima kasih telah order di CremenCrumb!\n\n";
                $customerMessage .= "📋 *DETAIL PESANAN*\n";
                $customerMessage .= "No. Order: {$order->order_number}\n";
                $customerMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
                $customerMessage .= "Status: *MENUNGGU PEMBAYARAN*\n\n";
                $customerMessage .= "💳 Silakan selesaikan pembayaran melalui Midtrans.\n";
                $customerMessage .= "🔗 " . route('payment.midtrans', $order) . "\n\n";
                $customerMessage .= "Terima kasih! 🙏";
                $this->whatsapp->send($order->customer_phone, $customerMessage);

                $adminPhone = env('ADMIN_WHATSAPP');
                if ($adminPhone) {
                    $adminMessage = "🆕 *ORDER BARU - MENUNGGU MIDTRANS*\n\n";
                    $adminMessage .= "No. Order: {$order->order_number}\n";
                    $adminMessage .= "Customer: {$order->customer_name}\n";
                    $adminMessage .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n";
                    $adminMessage .= "Status: *MENUNGGU PEMBAYARAN*\n\n";
                    $adminMessage .= "🔗 " . route('admin.orders.show', $order);
                    $this->whatsapp->send($adminPhone, $adminMessage);
                }
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()->route('payment.midtrans', $order);
        }

        return redirect()->route('order.success', $order)->with('success', 'Order placed successfully!');
    }

    private function cartWeight(array $cart): int
    {
        $weight = 0;
        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            $perItem = max(1, (int) ($product?->weight_gram ?? config('payment.rajaongkir.default_weight_gram', 1000)));
            $weight += $perItem * (int) $item['quantity'];
        }

        return max(1, $weight);
    }
}
