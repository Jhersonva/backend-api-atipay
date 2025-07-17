<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsHistory extends Model
{
    use HasFactory;
    protected $table = 'points_history';

    protected $fillable = [
        'user_id',
        'points',
        'source',
        'note',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
