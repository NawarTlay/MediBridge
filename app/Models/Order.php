<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_date',
        'store_id',
        'order_status',
        'payment_status',
        'seenNotifi',
    ];

    public function order_medicins(){

        return $this->hasMany(Order_medicin::class);
    }

    public function medicins(){
        return $this->belongsToMany(Medicin::class, 'order_medicins');
    }

    public function user(){

        return $this->belongsTo(User::class);
    }
}
