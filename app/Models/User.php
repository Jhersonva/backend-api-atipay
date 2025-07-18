<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\AtipayTransfer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PARTNER = 'partner';

    protected $fillable = [
        'username', 'email', 'password', 'role', 'status', 'accumulated_points', 'reference_code', 'referred_by',
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];

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

    /**
     * Transferencias enviadas por el usuario
     */
    public function sentTransfers()
    {
        return $this->hasMany(AtipayTransfer::class, 'sender_id');
    }

    /**
     * Transferencias recibidas por el usuario
     */
    public function receivedTransfers()
    {
        return $this->hasMany(AtipayTransfer::class, 'receiver_id');
    }

}
