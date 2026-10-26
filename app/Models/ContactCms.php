<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCms extends Model
{
    use HasFactory;

    protected $table = 'contact_cms';

    protected $fillable = [
        'hero',
        'banner_image',
        'info',
        'faq',
    ];

    protected $casts = [
        'hero' => 'array',
        'info' => 'array',
        'faq'  => 'array',
    ];
}