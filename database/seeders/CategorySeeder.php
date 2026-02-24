<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Entradas',
            'Pratos Principais',
            'Massas',
            'Carnes',
            'Peixes e Frutos do Mar',
            'Saladas',
            'Sobremesas',
            'Bebidas',
            'Bebidas Alcoólicas',
            'Porções',
        ];

        foreach($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
