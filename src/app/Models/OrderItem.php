<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_price',
        'quantity',
        'subtotal'
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    /**
     * Get the order that owns the item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}