<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroCms extends Model
{
    use HasFactory;

    protected $table = 'hero_cms';

    protected $fillable = [
        'label', 'title', 'description',
        'primary_btn_label', 'primary_btn_link',
        'secondary_btn_label', 'secondary_btn_link',
    ];
}
