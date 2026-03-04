<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Request $request) {
        $query = Table::orderBy('number');

        if($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get(), 200);
    }
    public function updateStatus(Request $request, Table $table){
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,reserved'],
        ]);

        $table->update($validated);

        return response()->json($table, 200);
    }
}
