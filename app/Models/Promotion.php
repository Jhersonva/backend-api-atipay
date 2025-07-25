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
        'duration_months',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
