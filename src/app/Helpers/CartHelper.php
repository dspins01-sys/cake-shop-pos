<?php

namespace App\Helpers;

use App\Models\Product;

class CartHelper
{
    /**
     * Get cart from session
     */
    public static function getCart()
    {
        return session()->get('cart', []);
    }

    /**
     * Add item to cart
     */
    public static function addToCart(Product $product, $quantity = 1)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image' => $product->image,
                'slug' => $product->slug,
                'stock' => $product->stock
            ];
        }
        
        session()->put('cart', $cart);
        return $cart;
    }

    /**
     * Update cart item quantity
     */
    public static function updateCart($id, $quantity)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$id])) {
            if ($quantity <= 0) {
                unset($cart[$id]);
            } else {
                $cart[$id]['quantity'] = $quantity;
            }
            session()->put('cart', $cart);
        }
        
        return $cart;
    }

    /**
     * Remove item from cart
     */
    public static function removeFromCart($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);
        return $cart;
    }

    /**
     * Clear cart
     */
    public static function clearCart()
    {
        session()->forget('cart');
    }

    /**
     * Get cart total
     */
    public static function getTotal()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }

    /**
     * Get cart count
     */
    public static function count()
    {
        return count(session()->get('cart', []));
    }

    /**
     * Get cart items count (total quantity)
     */
    public static function totalItems()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['quantity'];
        }
        
        return $total;
    }
}
