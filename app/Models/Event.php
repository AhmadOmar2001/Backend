<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $fillable = [
        'user_id',
        'hall_id',
        'event_name',
        'event_type',
        'start_date',
        'end_date',
        'seats_number',
        'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id', 'id');
    }

    public function eventOptions()
    {
        return $this->hasMany(EventOption::class, 'event_id', 'id');
    }

    public function options()
    {
        return $this->belongsToMany(Option::class, EventOption::class, 'event_id', 'option_id');
    }
}
