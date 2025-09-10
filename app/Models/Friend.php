<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    protected $table = 'friends';
    protected $fillable = [
        'user1_id',
        'user2_id',
        'status'
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id', 'id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id', 'id');
    }
}
