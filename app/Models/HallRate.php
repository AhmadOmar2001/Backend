<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallRate extends Model
{
    use HasFactory;

    protected $table = 'halls_rates';
    protected $fillable = [
        'user_id',
        'hall_id',
        'stars',
        'comment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id', 'id');
    }
}
