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
        $products = [
            // ══════════ Entradas (category_id: 1) ══════════
            ['category_id' => 1, 'name' => 'Bruschetta', 'price' => 22.90, 'description' => 'Pão italiano com tomate e manjericão'],
            ['category_id' => 1, 'name' => 'Carpaccio', 'price' => 34.90, 'description' => 'Carpaccio de carne com rúcula e parmesão'],
            ['category_id' => 1, 'name' => 'Camarão ao Alho', 'price' => 42.90, 'description' => 'Camarões salteados no alho e óleo'],
            ['category_id' => 1, 'name' => 'Ceviche', 'price' => 38.90, 'description' => 'Peixe branco marinado em limão'],
            ['category_id' => 1, 'name' => 'Bolinho de Bacalhau', 'price' => 28.90, 'description' => '6 unidades'],

            // ══════════ Pratos Principais (category_id: 2) ══════════
            ['category_id' => 2, 'name' => 'Filé Mignon', 'price' => 65.90, 'description' => 'Filé mignon grelhado com fritas'],
            ['category_id' => 2, 'name' => 'Frango Grelhado', 'price' => 42.90, 'description' => 'Peito de frango com legumes'],
            ['category_id' => 2, 'name' => 'Picanha na Brasa', 'price' => 78.90, 'description' => 'Picanha 400g com arroz e farofa'],
            ['category_id' => 2, 'name' => 'Costela BBQ', 'price' => 68.90, 'description' => 'Costela ao molho barbecue'],
            ['category_id' => 2, 'name' => 'Bife à Parmegiana', 'price' => 58.90, 'description' => 'Bife empanado com molho e queijo'],

            // ══════════ Massas (category_id: 3) ══════════
            ['category_id' => 3, 'name' => 'Spaghetti Carbonara', 'price' => 38.90, 'description' => 'Massa com molho carbonara'],
            ['category_id' => 3, 'name' => 'Lasanha Bolonhesa', 'price' => 42.90, 'description' => 'Lasanha com molho bolonhesa'],
            ['category_id' => 3, 'name' => 'Fettuccine Alfredo', 'price' => 44.90, 'description' => 'Massa ao molho branco'],
            ['category_id' => 3, 'name' => 'Penne ao Sugo', 'price' => 32.90, 'description' => 'Penne ao molho de tomate'],
            ['category_id' => 3, 'name' => 'Ravióli de Queijo', 'price' => 46.90, 'description' => 'Ravióli recheado ao molho rose'],

            // ══════════ Carnes (category_id: 4) ══════════
            ['category_id' => 4, 'name' => 'Alcatra Grelhada', 'price' => 52.90, 'description' => 'Alcatra 350g com guarnições'],
            ['category_id' => 4, 'name' => 'Maminha na Chapa', 'price' => 56.90, 'description' => 'Maminha 300g'],
            ['category_id' => 4, 'name' => 'Fraldinha Angus', 'price' => 72.90, 'description' => 'Fraldinha premium 400g'],
            ['category_id' => 4, 'name' => 'Medalhão ao Molho Madeira', 'price' => 68.90, 'description' => 'Medalhão 250g'],

            // ══════════ Peixes (category_id: 5) ══════════
            ['category_id' => 5, 'name' => 'Salmão Grelhado', 'price' => 64.90, 'description' => 'Salmão com risoto de limão'],
            ['category_id' => 5, 'name' => 'Tilápia na Manteiga', 'price' => 48.90, 'description' => 'Tilápia ao molho de manteiga'],
            ['category_id' => 5, 'name' => 'Moqueca de Peixe', 'price' => 58.90, 'description' => 'Moqueca capixaba'],
            ['category_id' => 5, 'name' => 'Camarão na Moranga', 'price' => 82.90, 'description' => 'Camarões ao creme na moranga'],

            // ══════════ Saladas (category_id: 6) ══════════
            ['category_id' => 6, 'name' => 'Salada Caesar', 'price' => 32.90, 'description' => 'Alface, croutons e parmesão'],
            ['category_id' => 6, 'name' => 'Salada Caprese', 'price' => 28.90, 'description' => 'Tomate, mussarela e manjericão'],
            ['category_id' => 6, 'name' => 'Salada Tropical', 'price' => 34.90, 'description' => 'Mix de folhas com frutas'],

            // ══════════ Sobremesas (category_id: 7) ══════════
            ['category_id' => 7, 'name' => 'Petit Gâteau', 'price' => 28.90, 'description' => 'Bolo de chocolate com sorvete'],
            ['category_id' => 7, 'name' => 'Pudim', 'price' => 18.90, 'description' => 'Pudim de leite condensado'],
            ['category_id' => 7, 'name' => 'Tiramisù', 'price' => 32.90, 'description' => 'Sobremesa italiana ao café'],
            ['category_id' => 7, 'name' => 'Cheesecake', 'price' => 26.90, 'description' => 'Torta de cream cheese'],
            ['category_id' => 7, 'name' => 'Brownie', 'price' => 24.90, 'description' => 'Brownie com sorvete'],
            ['category_id' => 7, 'name' => 'Torta de Limão', 'price' => 22.90, 'description' => 'Torta com merengue'],

            // ══════════ Bebidas (category_id: 8) ══════════
            ['category_id' => 8, 'name' => 'Suco Natural Laranja', 'price' => 12.90, 'description' => '500ml'],
            ['category_id' => 8, 'name' => 'Suco Natural Limão', 'price' => 11.90, 'description' => '500ml'],
            ['category_id' => 8, 'name' => 'Suco Natural Abacaxi', 'price' => 12.90, 'description' => '500ml'],
            ['category_id' => 8, 'name' => 'Refrigerante Lata', 'price' => 8.90, 'description' => '350ml'],
            ['category_id' => 8, 'name' => 'Refrigerante 1L', 'price' => 14.90, 'description' => '1 litro'],
            ['category_id' => 8, 'name' => 'Água Mineral', 'price' => 5.90, 'description' => '500ml'],
            ['category_id' => 8, 'name' => 'Água com Gás', 'price' => 7.90, 'description' => '500ml'],
            ['category_id' => 8, 'name' => 'Limonada Suíça', 'price' => 14.90, 'description' => '500ml'],
            ['category_id' => 8, 'name' => 'Chá Gelado', 'price' => 10.90, 'description' => '500ml'],

            // ══════════ Bebidas Alcoólicas (category_id: 9) ══════════
            ['category_id' => 9, 'name' => 'Cerveja Artesanal IPA', 'price' => 18.90, 'description' => '500ml'],
            ['category_id' => 9, 'name' => 'Cerveja Pilsen', 'price' => 12.90, 'description' => '500ml'],
            ['category_id' => 9, 'name' => 'Chopp Claro', 'price' => 14.90, 'description' => '300ml'],
            ['category_id' => 9, 'name' => 'Chopp Escuro', 'price' => 16.90, 'description' => '300ml'],
            ['category_id' => 9, 'name' => 'Caipirinha', 'price' => 22.90, 'description' => 'Limão'],
            ['category_id' => 9, 'name' => 'Caipirinha de Frutas', 'price' => 24.90, 'description' => 'Morango, kiwi ou maracujá'],
            ['category_id' => 9, 'name' => 'Vinho Tinto Taça', 'price' => 28.90, 'description' => '150ml'],
            ['category_id' => 9, 'name' => 'Vinho Branco Taça', 'price' => 26.90, 'description' => '150ml'],
            ['category_id' => 9, 'name' => 'Gin Tônica', 'price' => 32.90, 'description' => 'Gin premium'],

            // ══════════ Porções (category_id: 10) ══════════
            ['category_id' => 10, 'name' => 'Porção de Fritas', 'price' => 24.90, 'description' => 'Batata frita 500g'],
            ['category_id' => 10, 'name' => 'Porção de Onion Rings', 'price' => 28.90, 'description' => 'Anéis de cebola'],
            ['category_id' => 10, 'name' => 'Porção de Mandioca', 'price' => 22.90, 'description' => 'Mandioca frita 500g'],
            ['category_id' => 10, 'name' => 'Calabresa Acebolada', 'price' => 34.90, 'description' => '400g'],
            ['category_id' => 10, 'name' => 'Porção de Iscas de Frango', 'price' => 32.90, 'description' => '400g'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
