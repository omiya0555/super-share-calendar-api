<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name'          => 'apple',
                'email'         => 'apple@example.com',
                'password'      => Hash::make('password'),
                'user_icon_id'  => 1,
            ],
            [
                'name'          => 'banana',
                'email'         => 'banana@example.com',
                'password'      => Hash::make('password'),
                'user_icon_id'  => 2,
            ],
            [
                'name'          => 'orange',
                'email'         => 'orange@example.com',
                'password'      => Hash::make('password'),
                'user_icon_id'  => 3,
            ],
            [
                'name'          => 'lemon',
                'email'         => 'lemon@example.com',
                'password'      => Hash::make('password'),
                'user_icon_id'  => 4,
            ],
            [
                'name'          => 'testuser', // 検証用アカウント
                'email'         => 'test@gmail.com',
                'password'      => Hash::make('testuser'),
                'user_icon_id'  => 5,
            ],
        ]);
    }
}