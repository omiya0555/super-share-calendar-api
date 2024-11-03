<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run()
    {
        Event::create([
            'title'         => 'Sample Event',
            'start_time'    => '2023-11-14 07:00:00',
            'end_time'      => '2023-11-15 16:00:00',
            'all_day'       => false,
            'organizer_id'  => 5, // 検証 testuser
        ]);
    }
}