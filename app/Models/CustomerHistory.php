<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerHistory extends Model
{
    protected $table = 'customer_histories';
    protected $fillable = [
        'customer_id', 'type_history', 'content','created_by'
    ];
}
