<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoffeeProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori Dasar
        $categories = [
            ['name' => 'Coffee Beans', 'slug' => 'coffee-beans'],
            ['name' => 'Espresso Based', 'slug' => 'espresso-based'],
            ['name' => 'Non-Coffee', 'slug' => 'non-coffee'],
            ['name' => 'Snacks', 'slug' => 'snacks'],
        ];

        foreach ($categories as $cat) {
            $category = Category::create($cat);

            // 2. Tambahkan Produk Contoh berdasarkan Kategori
            if ($cat['name'] === 'Coffee Beans') {
                Product::create([
                    'category_id' => $category->id,
                    'name' => 'Arabica Gayo 250g',
                    'slug' => Str::slug('Arabica Gayo 250g'),
                    'description' => 'Biji kopi pilihan dari dataran tinggi Gayo.',
                    'price' => 95000,
                    'stock' => 50,
                ]);
            }

            if ($cat['name'] === 'Espresso Based') {
                Product::create([
                    'category_id' => $category->id,
                    'name' => 'Caffe Latte',
                    'slug' => Str::slug('Caffe Latte'),
                    'description' => 'Espresso dengan susu uap panas.',
                    'price' => 28000,
                    'stock' => 999, // Stock melimpah untuk minuman
                ]);
                
                Product::create([
                    'category_id' => $category->id,
                    'name' => 'Americano',
                    'slug' => Str::slug('Americano'),
                    'description' => 'Double shot espresso dengan air panas.',
                    'price' => 22000,
                    'stock' => 999,
                ]);
            }

            if ($cat['name'] === 'Non-Coffee') {
                Product::create([
                    'category_id' => $category->id,
                    'name' => 'Matcha Latte',
                    'slug' => Str::slug('Matcha Latte'),
                    'description' => 'Bubuk matcha premium dengan susu segar.',
                    'price' => 30000,
                    'stock' => 999,
                ]);
            }
        }
    }
}
