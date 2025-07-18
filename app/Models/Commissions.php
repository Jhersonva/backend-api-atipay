<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commissions extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referred_us',
        'level',
        'points_earned',
        'total_amount',
        'month',
        'generation_date'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
