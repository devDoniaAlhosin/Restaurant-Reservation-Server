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
                'name' => 'Steak Dinner',
                'description' => 'Juicy steak with mashed potatoes',
                'price' => 19.99,
                'image' => 'https://media.istockphoto.com/id/1245334812/photo/homemade-shawarma-steak-with-quinoa-tabbouleh-and-tahini-sauce.jpg?s=612x612&w=0&k=20&c=jbpxCbcTtpFcEdCIJk7Yy0RhSyCzl91riZH8lFcBYUA=',
                'category_name' => 'main_dish',
            ],
            [
                'name' => 'Grilled Chicken',
                'description' => 'Grilled chicken with herbs and spices',
                'price' => 14.99,
                'image' => 'https://media.istockphoto.com/id/928823336/photo/grilled-chicken-breast-fried-chicken-fillet-and-fresh-vegetable-salad-of-tomatoes-cucumbers.jpg?s=612x612&w=0&k=20&c=x6KbcglhT_oxKEzCoSM5E8abP3rlEZAt7jQhlAPZtoY=',
                'category_name' => 'main_dish',
            ],
            [
                'name' => 'Pasta Alfredo',
                'description' => 'Creamy Alfredo pasta with mushrooms',
                'price' => 12.99,
                'image' => 'https://media.istockphoto.com/id/1213501869/photo/cheese-ravioli-with-sausage-and-mushrooms-in-a-rosemary-cream-sauce.jpg?s=612x612&w=0&k=20&c=1CMvFQNOTSaj08czfmjDUfcdOjaujpI3Y-sGsJ8FFs4=',
                'category_name' => 'main_dish',
            ],
            [
                'name' => 'Cheeseburger',
                'description' => 'Juicy cheeseburger with fries',
                'price' => 9.99,
                'image' => 'https://media.istockphoto.com/id/1215569804/photo/fresh-and-juicy-hamburger-on-a-paper-pillow-with-beer-on-a-wooden-table-dark-background.jpg?s=612x612&w=0&k=20&c=9sZEpxLpqjsPjZUaiGgG0BIkbfowzIb4jeIPggR8pcM=',
                'category_name' => 'main_dish',
            ],
            [
                'name' => 'Vegan Salad',
                'description' => 'Fresh mixed greens with avocado',
                'price' => 8.99,
                'image' => 'https://media.istockphoto.com/id/1047798504/photo/bowl-dish-with-brown-rice-cucumber-tomato-green-peas-red-cabbage-chickpea-fresh-lettuce-salad.jpg?s=612x612&w=0&k=20&c=xAXkGII7E_NJ_JH2Sz9oy307EbowN5u_UODDM1K019g=',
                'category_name' => 'main_dish',
            ],
            [
                'name' => 'Breakfast Burrito',
                'description' => 'Eggs, cheese, and sausage in a wrap',
                'price' => 7.99,
                'image' => 'https://media.istockphoto.com/id/865026142/photo/ramon-noodle-egg-and-sausage-breakfast-burrito.jpg?s=612x612&w=0&k=20&c=VfrrmfumsKJOk5odeftutieqz37P0LRIPcgsECjcoZw=',
                'category_name' => 'breakfast',
            ],
            [
                'name' => 'French Toast',
                'description' => 'French toast with syrup and berries',
                'price' => 6.99,
                'image' => 'https://media.istockphoto.com/id/1225582525/photo/traditional-french-toasts-with-blueberries-strawberries-and-powdered-sugar-on-white-plate.jpg?s=612x612&w=0&k=20&c=5DWhiXmrjaEJ9ibt7jWO9BmiSjT_BZr6btpg2yOP9CA=',
                'category_name' => 'breakfast',
            ],
            [
                'name' => 'Omelette',
                'description' => 'Three-egg omelette with vegetables and cheese',
                'price' => 6.50,
                'image' => 'https://media.istockphoto.com/id/1631661154/photo/breakfast-omelette-with-sausage-sweet-peppers-and-sandwich-with-marinated-red-onions-frittata.jpg?s=612x612&w=0&k=20&c=mWkVSzv3PqEep6k9ZmEHdAEk5YZoJSOwZgYzyZruOmw=',
                'category_name' => 'breakfast',
            ],
            [
                'name' => 'Pancakes',
                'description' => 'Fluffy pancakes with syrup',
                'price' => 5.99,
                'image' => 'https://media.istockphoto.com/id/161170090/photo/pancakes-with-berries-and-maple-syrup.jpg?s=612x612&w=0&k=20&c=8EctScsN7q5UmxeXPNBnYN1eFmJmgmp2bE0OIq_czwM=',
                'category_name' => 'breakfast',
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Crispy romaine with Caesar dressing',
                'price' => 7.99,
                'image' => 'https://media.istockphoto.com/id/1488094618/photo/a-delicious-chicken-caesar-salad-with-parmesan-cheese-tomatoes-croutons-and-dressing.jpg?s=612x612&w=0&k=20&c=8miw7Eulhe2HBM1Gtk8c7Z2xzJTtXsFBHroJYIoUGc8=',
                'category_name' => 'main_dish',
            ],
            [
                'name' => 'Tacos',
                'description' => 'Soft tacos with beef and fresh toppings',
                'price' => 9.50,
                'image' => 'https://media.istockphoto.com/id/1483876192/photo/delicious-street-tacos-with-juicy-birria-or-savory-carne-asada-on-a-soft-corn-tortilla-topped.jpg?s=612x612&w=0&k=20&c=eLperppfO5sYPYRt8eQm22ZhGJZ10pw-BzRFcWQm9DM=',
                'category_name' => 'main_dish',
            ],
            [
                'name' => 'Chocolate Milkshake',
                'description' => 'Thick chocolate milkshake with whipped cream',
                'price' => 4.99,
                'image' => 'https://media.istockphoto.com/id/477812744/photo/chocolate-milkshake-on-rustic-wood-table.jpg?s=612x612&w=0&k=20&c=sUQI2qNzYorRM8lwDeXiwoGA6gNFKSTOXaytWY1N39w=',
                'category_name' => 'drink',
            ],
            [
                'name' => 'Iced Coffee',
                'description' => 'Cold brew coffee with ice',
                'price' => 3.99,
                'image' => 'https://media.istockphoto.com/id/155370125/photo/iced-coffee.jpg?s=612x612&w=0&k=20&c=H3THiHl4pvB8tJ6C3eGI2YFjQKZqZLZwAgQW_vRjAk0=',
                'category_name' => 'drink',
            ],
            [
                'name' => 'Lemonade',
                'description' => 'Fresh lemonade with a slice of lemon',
                'price' => 2.99,
                'image' => 'https://media.istockphoto.com/id/1401150816/photo/two-glasses-of-lemonade-with-mint-and-lemons.jpg?s=612x612&w=0&k=20&c=LuuInHwGO11q3aBbAmyMy4JvZ3njV4R0IRE10klTLew=',
                'category_name' => 'drink',
            ],
            [
                'name' => 'Coca-Cola',
                'description' => 'Chilled Coca-Cola in a bottle',
                'price' => 1.99,
                'image' => 'https://media.istockphoto.com/id/1281410543/photo/pouring-cola-from-bottle-into-glass-and-fizz-with-ice-cubes-on-table-against-blurred.jpg?s=612x612&w=0&k=20&c=chJr_K50Z4M776qv5x0jZmZ5nTuCjuckBWcBlrMGazI=',
                'category_name' => 'drink',
            ],
            [
                'name' => 'Chocolate Cake',
                'description' => 'Rich chocolate cake with layers of fudge',
                'price' => 5.99,
                'image' => 'https://media.istockphoto.com/id/1327828405/photo/delicious-slice-of-cake.jpg?s=612x612&w=0&k=20&c=WZ8fyg1Yj2vsqab0Px5o1vVHUxGWJYix7kBkLFki5Ag=',
                'category_name' => 'dessert',
            ],
            [
                'name' => 'Apple Pie',
                'description' => 'Classic apple pie with a flaky crust',
                'price' => 4.99,
                'image' => 'https://media.istockphoto.com/id/1175241902/photo/slice-of-apple-pie.jpg?s=612x612&w=0&k=20&c=DL3ioqFWju1HMSPXhGG0RwMRBwadSFxw_yptRrBCFgo=',
                'category_name' => 'dessert',
            ],
            [
                'name' => 'Cheesecake',
                'description' => 'Creamy cheesecake with a graham cracker crust',
                'price' => 6.50,
                'image' => 'https://media.istockphoto.com/id/1443993064/photo/fall-cheesecake-with-figs-and-maple-syrup.jpg?s=612x612&w=0&k=20&c=UGx-WiX2Wlbs7EG7BkrahChpHpaB4nI1hwLssNSsuJk=',
                'category_name' => 'dessert',
            ],
            [
                'name' => 'Mango Smoothie',
                'description' => 'Refreshing mango smoothie',
                'price' => 4.50,
                'image' => 'https://media.istockphoto.com/id/1154973292/photo/fresh-mango-smoothie-with-mango-fruits.jpg?s=612x612&w=0&k=20&c=TPUIATbB7LX-xrS2yGuwPqjFdvRK2-92QSEY_abRz4I=',
                'category_name' => 'drink',
            ],
            [
                'name' => 'Ice Cream Sundae',
                'description' => 'Vanilla ice cream topped with chocolate syrup and sprinkles',
                'price' => 3.99,
                'image' => 'https://media.istockphoto.com/id/1919777534/photo/a-sundae-ice-cream-on-a-summer-day.jpg?s=612x612&w=0&k=20&c=M_QI0usOhUL-tRygR3uswFGgL9X6Hmoo-7vaTRyHPB4=',
                'category_name' => 'dessert',
            ]
        ]);
    }
}
