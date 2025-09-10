<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteHall extends Model
{
    use HasFactory;

    protected $table = 'favorite_halls';
    protected $fillable = [
        'user_id',
        'hall_id'
    ];
}
