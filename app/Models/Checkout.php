<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'phone', 'country', 'address', 'city', 'postal_code',
        'shipping_method', 'shipping_price', 'subtotal', 'total',
        'payment_method', 'payment_status', 'transaction_id', 'paid_at', 'status',
        'tracking_number', 'label_url', 'shipped_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function items()
    {
        return $this->hasMany(CheckoutItem::class);
    }
}
