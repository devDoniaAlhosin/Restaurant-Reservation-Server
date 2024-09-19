<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

        public function run()
    {
        DB::table('bookings')->insert([
            [
                'user_id' => 1,
                'username' => 'John Doe',
                'phone' => '555-1234',
                'date_time' => Carbon::createFromFormat('m/d/Y h:i A', '09/28/2024 12:00 PM'),
                'total_person' => 4,
                'status' => 'pending',
                'notes' => 'Requesting a window seat',
            ],
        ]);
    }

}
