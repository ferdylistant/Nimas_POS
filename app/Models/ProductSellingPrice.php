<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSellingPrice extends Model
{
    protected $table = 'product_selling_prices';
    protected $fillable = [
        'product_id', 'type', 'selling_price'

    ];
}
