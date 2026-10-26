<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxId extends Model
{
    use HasFactory;

    protected $table = 'tax_ids';

    protected $fillable = [
        'user_id',
        'tax_id',
        'status',
    ];

    // Relation with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
