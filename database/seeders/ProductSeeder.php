<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $products = [
            [
                // Entradas (category_id: 1)
                ['category_id' => 1, 'name' => 'Bruschetta', 'price' => 22.90, 'description' => 'Pão italiano com tomate e manjericão'],
                ['category_id' => 1, 'name' => 'Carpaccio', 'price' => 34.90, 'description' => 'Carpaccio de carne com rúcula e parmesão'],

                // Pratos Principais (category_id: 2)
                ['category_id' => 2, 'name' => 'Filé Mignon', 'price' => 65.90, 'description' => 'Filé mignon grelhado com fritas'],
                ['category_id' => 2, 'name' => 'Frango Grelhado', 'price' => 42.90, 'description' => 'Peito de frango grelhado com legumes'],

                // Massas (category_id: 3)
                ['category_id' => 3, 'name' => 'Spaghetti Carbonara', 'price' => 38.90, 'description' => 'Massa com molho carbonara'],
                ['category_id' => 3, 'name' => 'Lasanha Bolonhesa', 'price' => 42.90, 'description' => 'Lasanha com molho bolonhesa'],

                // Sobremesas (category_id: 7)
                ['category_id' => 7, 'name' => 'Petit Gâteau', 'price' => 28.90, 'description' => 'Bolo de chocolate com sorvete'],
                ['category_id' => 7, 'name' => 'Pudim', 'price' => 18.90, 'description' => 'Pudim de leite condensado'],

                // Bebidas (category_id: 8)
                ['category_id' => 8, 'name' => 'Suco Natural', 'price' => 12.90, 'description' => 'Suco natural de laranja'],
                ['category_id' => 8, 'name' => 'Refrigerante', 'price' => 8.90, 'description' => 'Lata 350ml'],
                ['category_id' => 8, 'name' => 'Água Mineral', 'price' => 5.90, 'description' => '500ml'],

                // Bebidas Alcoólicas (category_id: 9)
                ['category_id' => 9, 'name' => 'Cerveja Artesanal', 'price' => 18.90, 'description' => 'IPA 500ml'],
                ['category_id' => 9, 'name' => 'Caipirinha', 'price' => 22.90, 'description' => 'Caipirinha de limão'],
            ]
        ];

        foreach($products as $product) {
            foreach($product as $product) {
                Product::create($product);
            }
        }
    }
}
