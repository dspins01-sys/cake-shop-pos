<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price', 'weight_gram',
        'stock', 'is_active', 'image', 'category_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight_gram' => 'integer',
        'is_active' => 'boolean',
        'stock' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);
        });

        static::updating(function ($product) {
            $product->slug = Str::slug($product->name);
        });
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        return 'https://via.placeholder.com/300x300?text=No+Image';
    }

    public function getAvailableStockAttribute()
    {
        $pendingOrders = \App\Models\OrderItem::where('product_id', $this->id)
            ->whereHas('order', function($q) {
                $q->whereIn('status', ['pending', 'waiting_confirmation']);
            })
            ->sum('quantity');

        return $this->stock - $pendingOrders;
    }

    public function getPendingOrdersCountAttribute()
    {
        return \App\Models\OrderItem::where('product_id', $this->id)
            ->whereHas('order', function($q) {
                $q->whereIn('status', ['pending', 'waiting_confirmation']);
            })
            ->sum('quantity');
    }
}
