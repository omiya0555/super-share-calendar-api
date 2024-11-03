<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserIconSeeder::class,
            UserSeeder::class,
            EventSeeder::class,
            CommentSeeder::class,
            ChatRoomSeeder::class,
        ]);
    }
}