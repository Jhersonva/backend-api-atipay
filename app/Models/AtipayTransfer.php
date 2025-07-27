<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtipayTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'type',
        'confirmed',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    //El usuario que envió los Atipay
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    //El usuario que recibió los Atipay
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
