<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopCms extends Model
{
    use HasFactory;

    protected $table = 'shop_cms';

    protected $fillable = [
        'eyebrow', 'title', 'description', 'banner_image'
    ];
}
