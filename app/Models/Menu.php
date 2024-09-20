<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description',
        'price',
        'image',
        'category_name',

    ];
//     protected $casts = [
//         'price' => 'decimal:2',
//         'category_name' => 'string', // Enum as string
//     ];

//     // The scopeCategory() method allows you to easily filter menus based on
//     // the category_name. For example,
//     // if you want to retrieve all breakfast items, you can use this scope.
//     public function scopeCategory($query, $category)
//     {
//         return $query->where('category_name', $category);
//     }
// }
}
