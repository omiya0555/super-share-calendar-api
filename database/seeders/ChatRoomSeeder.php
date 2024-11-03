<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatRoom;
use App\Models\ChatRoomUserUser;
use App\Models\User;

class ChatRoomSeeder extends Seeder
{
    public function run()
    {
        // apple と testuser を取得
        $apple      = User::where('name', 'apple')   ->first();
        $testuser   = User::where('name', 'testuser')->first();

        if ($apple && $testuser) {
            $chatRoom = ChatRoom::create([
                'room_name' => null,
                'is_group'  => false,
            ]);
            // apple と testuser を追加
            $chatRoom->users()->attach([$apple->id, $testuser->id]);
        }
    }
}
