<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('notifications')->insert([
            [
                'user_id' => 1,
                'message' => 'Your booking has been confirmed!',
                'is_read' => false,
            ],
        ]);
    }
}
