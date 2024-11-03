<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserIconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_icons')->insert([
            ['icon_url' => '/images/icons/apple.png'],
            ['icon_url' => '/images/icons/banana.png'],
            ['icon_url' => '/images/icons/orange.png'],
            ['icon_url' => '/images/icons/lemon.png'],
            ['icon_url' => '/images/icons/testuser.png'],
        ]);
    }
}