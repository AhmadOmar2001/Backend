<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    use HasFactory;

    protected $table = 'halls';
    protected $fillable = [
        'user_id',
        'hall_name',
        'seats_number',
        'location',
        'description'
    ];

    public function images()
    {
        return $this->hasMany(HallImage::class, 'hall_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(Option::class, 'hall_id', 'id');
    }

    public function rates()
    {
        return $this->hasMany(HallRate::class, 'hall_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'hall_id', 'id');
    }
}
