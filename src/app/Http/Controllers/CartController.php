<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Helpers\CartHelper;
use Illuminate\Http\Request;

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
    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock
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
    public function checkout()
    {
        $cart = CartHelper::getCart();
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }
        
        $total = CartHelper::getTotal();
        
        return view('cart.checkout', compact('cart', 'total'));
    }
}
