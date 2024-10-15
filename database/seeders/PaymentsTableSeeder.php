<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PaymentsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('payments')->insert([
            [
                'booking_id' => 1,
                'user_id' => 1,
                'amount' => 23.98,
                'payment_method' => 'stripe',
                'payment_status' => 'completed',
                'payment_date' => now(),
            ],
        ]);
    }
}
