<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitedUser extends Model
{
    use HasFactory;

    protected $table = 'invited_users';
    protected $fillable = [
        'event_id',
        'user_id',
        'is_accepted'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}