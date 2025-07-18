<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'product_categories',
        'description',
        'price',
        'required_points',
        'stock',
        'status',
        'type',
        'image',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function category()
    {
        return $this->belongsTo(Product_Categories::class, 'product_categories');
    }
}