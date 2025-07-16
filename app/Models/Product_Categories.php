<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_Categories extends Model
{
    protected $table = 'product_categories';
    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
