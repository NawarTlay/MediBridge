<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourit extends Model
{
    use HasFactory;

    protected $table = "favourites";

    protected $fillable = [
        'user_id',
        'medicin_id',
    ];
}
