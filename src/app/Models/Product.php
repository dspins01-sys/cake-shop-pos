<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price', 
        'stock', 'is_active', 'image', 'category_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer'
    ];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Auto-generate slug dari name
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

    // Accessor untuk format price
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Accessor untuk image URL
public function getImageUrlAttribute()
{
    if ($this->image) {
        // Pastikan path-nya bener
        return asset('storage/' . $this->image);
    }

    // Return placeholder image kalo gak ada gambar
    return 'https://via.placeholder.com/300x300?text=No+Image';
}
/**
 * Get available stock (real stock - pending orders)
 */
public function getAvailableStockAttribute()
{
    // Hitung semua quantity dari order PENDING & WAITING_CONFIRMATION
    $pendingOrders = \App\Models\OrderItem::where('product_id', $this->id)
        ->whereHas('order', function($q) {
            $q->whereIn('status', ['pending', 'waiting_confirmation']);
        })
        ->sum('quantity');
    
    return $this->stock - $pendingOrders;
}

/**
 * Get pending orders count for display
 */
public function getPendingOrdersCountAttribute()
{
    return \App\Models\OrderItem::where('product_id', $this->id)
        ->whereHas('order', function($q) {
            $q->whereIn('status', ['pending', 'waiting_confirmation']);
        })
        ->sum('quantity');
}
}
