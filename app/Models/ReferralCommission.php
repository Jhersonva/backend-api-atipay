<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ReferralCommission extends Model
{
    protected $fillable = [
        'user_id',
        'referred_user_id',
        'level',
        'commission_amount',
        'points_generated',
        'source_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
