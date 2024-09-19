<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class MenusTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('menus')->insert([
            [
                'name' => 'Pancakes',
                'description' => 'Fluffy pancakes with syrup',
                'price' => 5.99,
                'image' => 'pancakes.jpg',
                'category_name' => 'breakfast',
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Crispy romaine with Caesar dressing',
                'price' => 7.99,
                'image' => 'caesar_salad.jpg',
                'category_name' => 'lunch',
            ],
            [
                'name' => 'Steak Dinner',
                'description' => 'Juicy steak with mashed potatoes',
                'price' => 19.99,
                'image' => 'steak_dinner.jpg',
                'category_name' => 'dinner',
            ],
        ]);
    }
}
