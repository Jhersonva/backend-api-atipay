<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promotion_id',
        'amount',
        'receipt_path',
        'status',
        'admin_message',
        'daily_earning',
        'approved_at',
        'start_date',
        'end_date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'daily_earning' => 'decimal:2',
        'approved_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(InvestmentWithdrawal::class);
    }
}
