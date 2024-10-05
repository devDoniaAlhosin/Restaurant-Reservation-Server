<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        return Menu::paginate($perPage);
    }


    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:50'],
            'category_name' => ['required', 'in:breakfast,main_dish,drink,dessert'],
            'price' => ['required', 'numeric'],
            'image' => ['required', 'string']
        ]);

        return Menu::create($fields);

    }

    public function show(Menu $menu){
        return $menu;
    }

    public function update(Request $request , Menu $menu){
        $fields = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:50'],
            'category_name' => ['required', 'in:breakfast,main_dish,drink,dessert'],
            'price' => ['required', 'numeric'],
            'image' => ['required', 'string']
        ]);
        $menu = $menu->update($fields);
        Log::info('Incoming request data:', $request->all());

        return $menu;
    }

    public function destroy(Menu $menu){
        $menu->delete();
    }


}
