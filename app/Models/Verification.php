<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Verification extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'verifications';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'account_type',
        'code',
        'expiry_date'
    ];
}