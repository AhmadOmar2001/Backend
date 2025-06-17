<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name' => 'Ahmad',
            'last_name' => 'Isa',
            'email' => 'ahmad@gmail.com',
            'password' => Hash::make('123456789'),
            'account_type' => 'regular_user',
        ]);
        User::create([
            'first_name' => 'Musa',
            'last_name' => 'Mohammad',
            'email' => 'musa@gmail.com',
            'password' => Hash::make('123456789'),
            'account_type' => 'service_provider',
        ]);
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'),
            'account_type' => 'admin',
        ]);
    }
}
