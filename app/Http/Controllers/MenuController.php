<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

/**
 * @OA\Info(title="Menu API", version="1.0")
 */
class MenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/menu",
     *     tags={"Menu"},
     *     summary="Get a list of menu items",
     *     description="Fetches a paginated list of all menu items. Accessible by anyone.",
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Number of items per page"
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 5);
        return Menu::paginate($perPage);
    }

    /**
     * @OA\Post(
     *     path="/api/menu",
     *     tags={"Menu"},
     *     summary="Create a new menu item",
     *     description="Creates a new menu item. Accessible only by admins.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Pasta"),
     *             @OA\Property(property="description", type="string", maxLength=50, example="Delicious pasta with sauce"),
     *             @OA\Property(property="category_name", type="string", enum={"breakfast", "main_dish", "drink", "dessert"}),
     *             @OA\Property(property="price", type="number", format="float", example=9.99),
     *             @OA\Property(property="image", type="string", example="image-url.jpg")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/menu/{menu}",
     *     tags={"Menu"},
     *     summary="Get a specific menu item",
     *     description="Fetches details of a specific menu item by ID. Accessible by anyone.",
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Menu item ID"
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(Menu $menu)
    {
        return $menu;
    }

    /**
     * @OA\Put(
     *     path="/api/menu/{menu}",
     *     tags={"Menu"},
     *     summary="Update an existing menu item",
     *     description="Updates an existing menu item by ID. Accessible only by admins.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Menu item ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Pasta"),
     *             @OA\Property(property="description", type="string", maxLength=50, example="Updated description"),
     *             @OA\Property(property="category_name", type="string", enum={"breakfast", "main_dish", "drink", "dessert"}),
     *             @OA\Property(property="price", type="number", format="float", example=10.99),
     *             @OA\Property(property="image", type="string", example="updated-image-url.jpg")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function update(Request $request, Menu $menu)
    {
        $fields = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:50'],
            'category_name' => ['required', 'in:breakfast,main_dish,drink,dessert'],
            'price' => ['required', 'numeric'],
            'image' => ['required', 'string']
        ]);
        $menu->update($fields);
        return $menu;
    }

    /**
     * @OA\Delete(
     *     path="/api/menu/{menu}",
     *     tags={"Menu"},
     *     summary="Delete a menu item",
     *     description="Deletes a specific menu item by ID. Accessible only by admins.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Menu item ID"
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return response()->json(['message' => 'Menu item deleted successfully.'], 200);
    }
}
