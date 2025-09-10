<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'account_type',
        'status'
    ];

    public function halls()
    {
        return $this->hasMany(Hall::class, 'user_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'user_id', 'id');
    }

    public function invitations()
    {
        return $this->belongsToMany(Event::class, 'invited_users', 'user_id', 'event_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    public function favoriteHalls()
    {
        return $this->belongsToMany(Hall::class, 'favorite_halls', 'user_id', 'hall_id');
    }
}
