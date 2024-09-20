<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        return Menu::all();
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

        if ($menu) {
            return response()->json($menu, 201); // Respond with created status
        } else {
            return response()->json(['error' => 'Failed to create menu'], 500); // Respond with error status
        }
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
        return $menu;
    }

    public function destroy(Menu $menu){
        $menu->delete();
    }


}
