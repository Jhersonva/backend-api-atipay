<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'amount',
        'status',
        'admin_message',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // Relaciones
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
