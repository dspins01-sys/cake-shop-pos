<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    'order_number',
    'tracking_code', // <-- PASTIKAN INI ADA
    'customer_name',
    'customer_email',
    'customer_phone',
    'address',
    'total',
    'status',
    'payment_method',
    'payment_status',
    'payment_proof',
    'paid_at',
    'notes',
    'admin_notes',
    'expired_at',
    'user_id'
];

    protected $casts = [
        'paid_at' => 'datetime',
        'total' => 'decimal:2'
    ];

    /**
     * Get the items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate order number
     */
    public static function generateOrderNumber()
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $lastOrder = self::whereDate('created_at', today())->count();
        $number = str_pad($lastOrder + 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$number}";
    }
}