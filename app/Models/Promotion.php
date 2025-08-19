<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'percentaje',
        'atipay_price_promotion',
        'duration_months',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'atipay_price_promotion' => 'float',
        'percentaje' => 'float',
    ];
}
