<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutCms extends Model
{
    use HasFactory;

    protected $table = 'about_cms';

    protected $fillable = [
        'hero', 'stats', 'banner_image', 'story', 'mission_vision', 'cta'
    ];

    protected $casts = [
        'hero' => 'array',
        'stats' => 'array',
        'story' => 'array',
        'mission_vision' => 'array',
        'cta' => 'array',
    ];
}
