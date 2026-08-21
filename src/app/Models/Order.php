<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'tracking_code', 'customer_name', 'customer_email',
        'customer_phone', 'address', 'total', 'shipping_cost', 'shipping_weight',
        'shipping_destination_id', 'shipping_province', 'shipping_city',
        'shipping_district', 'courier', 'courier_service', 'shipping_etd',
        'status', 'payment_method', 'payment_status', 'payment_proof', 'paid_at',
        'midtrans_token', 'midtrans_transaction_id', 'midtrans_payment_type',
        'midtrans_status', 'midtrans_paid_at', 'notes', 'admin_notes', 'expired_at',
        'user_id'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'midtrans_paid_at' => 'datetime',
        'total' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateOrderNumber()
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $lastOrder = self::whereDate('created_at', today())->count();
        $number = str_pad($lastOrder + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$number}";
    }
}
