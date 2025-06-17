<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallImage extends Model
{
    use HasFactory;

    protected $table = 'hall_images';
    protected $fillable = [
        'user_id',
        'hall_id',
        'image',
    ];
}