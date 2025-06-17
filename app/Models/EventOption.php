<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventOption extends Model
{
    use HasFactory;

    protected $table = 'events_options';
    protected $fillable = [
        'event_id',
        'option_id'
    ];
}