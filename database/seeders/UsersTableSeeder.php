<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'username' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'address' => '123 Main St',
                'phone' => '555-1234',
                'image' => null,
                'role' => 'user',
            ],
            [
                'username' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'address' => '456 Elm St',
                'phone' => '555-5678',
                'image' => null,
                'role' => 'admin',
            ],
        ]);
    }

}
