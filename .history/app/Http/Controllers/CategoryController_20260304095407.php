<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return response()->json($categories, 200);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }
    public function update(Request $request, Category $category) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
        ]);

        $category->update($validated);

        return response()->json($category, 200);
    }
    public function destroy(Category $category){
        if($category->products()->exists()){
            return response()->json(['message' => 'Não é possível excluir uma categoria que possui produtos vinculados.'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Categoria excluída com sucesso.'], 204);
    }
}
