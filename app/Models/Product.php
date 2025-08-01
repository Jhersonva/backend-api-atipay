<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'points',
        'unit_type',
        'stock',
        'status',
        'image_path',
        'type'
    ];

    protected $hidden = ['created_at', 'updated_at', 'image_path'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }

    public function course()
    {
        return $this->hasOne(Course::class);
    }
}

