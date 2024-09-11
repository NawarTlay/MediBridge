<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_medicin extends Model
{
    use HasFactory;

    protected $fillable= ['order_id', 'quantity', 'medicin_id'];
}
