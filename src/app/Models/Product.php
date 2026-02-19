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
}
