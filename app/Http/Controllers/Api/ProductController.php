<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Kita ambil produk beserta relasi images-nya
        $products = Product::with('images')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'description' => $product->description,
                'stock' => $product->stock,
                // Ambil gambar pertama jika ada, jika tidak pakai placeholder
                'image' => $product->images->first() 
                    ? asset('storage/' . $product->images->first()->image_path) 
                    : 'https://via.placeholder.com/500x500?text=No+Image',
            ];
        });

        return response()->json($products);
    }
}