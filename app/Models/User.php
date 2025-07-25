<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\AtipayTransfer;
use App\Models\Withdrawal;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PARTNER = 'partner';

    protected $fillable = [
        'username', 'email', 'password', 'role', 'status', 'atipay_investment_balance', 'atipay_store_balance', 'accumulated_points', 'withdrawable_balance', 'reference_code', 'referred_by',
    ];

    protected $appends = ['referral_url'];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];

    // Devuelve la URL construida
    public function getReferralUrlAttribute()
    {
        return url("/atipay/{$this->username}/reference-code-register/{$this->reference_code}");
    }

    // Mutador para encriptar la contraseña
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Referido por
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
    
    // Referencias a otros usuarios
    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    
    // Transferencias enviadas por el usuario
    public function sentTransfers()
    {
        return $this->hasMany(AtipayTransfer::class, 'sender_id');
    }

    // Transferencias recibidas por el usuario
    public function receivedTransfers()
    {
        return $this->hasMany(AtipayTransfer::class, 'receiver_id');
    }

    // Relacion con la cuenta del usuario
     
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    // Recargas solicitadas por el usuario
    public function atipayRecharges()
    {
        return $this->hasMany(AtipayRecharge::class);
    }
}
