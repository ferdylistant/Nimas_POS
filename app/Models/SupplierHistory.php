<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierHistory extends Model
{
    protected $table = 'supplier_histories';
    protected $fillable = [
        'supplier_id', 'type_history', 'content','created_by'
    ];
}
