<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    protected $table = 'product_suppliers';
    protected $fillable = [
        'product_id', 'supplier_id', 'product_qty','buying_price', 'buying_date'

    ];
}
