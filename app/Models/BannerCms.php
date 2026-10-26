<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerCms extends Model
{
    use HasFactory;

    protected $table = 'banner_cms';

    protected $fillable = ['image', 'cards'];

    protected $casts = [
        'cards' => 'array',
    ];
}
