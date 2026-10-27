<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutItem extends Model
{
    protected $fillable = [
        'checkout_id', 'product_id', 'product_name', 'price', 'quantity', 'subtotal'
    ];
}
