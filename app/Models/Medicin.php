<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicin extends Model
{
    use HasFactory;

    protected $fillable = [
        'sc_name',
        'trad_name',
        'finish_date',
        'user_id',
        'quantity',
        'price',
        'manufacturer',
        'category_id',
    ];

    public function favourits(){

        return $this->hasMany(Favourit::class);
    }

    public function order_medicins(){

        return $this->hasMany(Order_medicin::class);
    }

    public function orders(){
        return $this->belongsToMany(Order::class, 'order_medicins');
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
