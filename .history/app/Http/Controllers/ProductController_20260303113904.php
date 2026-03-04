<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // filtro p/ categoria
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // filtro p/ ativo
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // busca p/ nome
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json($product->load('category'), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $hasOrders = OrderItem::where('product_id', $product->id)->exists();

        if ($hasOrders) {
            $product->update(['is_active' => false]);

            return response()->json([
                'message' => 'Produto desativado com sucesso (possui histórico de vendas).',
                'product' => $product
            ]);
        }

        $product->delete();

        return response()->json([
            'message' => 'Produto excluido com sucesso.',
            'product' => $product
        ]);
    }

    public function salesStats(Product $product, Request $request)
    {
        $weeklySales = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [
                Carbon::now()->subWeeks(4)->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->select(
                DB::raw('YEARWEEK(orders.created_at, 1) as week'),
                DB::raw('MIN(DATE(orders.created_at)) as week_start'),
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        $monthlySales = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [
                Carbon::now()->subMonths(6)->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->select(
                DB::raw('YEAR(orders.created_at) as year'),
                DB::raw('MONTH(orders.created_at) as month'),
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $totals = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.status', 'paid')
            ->select(
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->first();

        return response()->json([
            'weeklySales' => $weeklySales,
            'monthlySales' => $monthlySales,
            'totals' => $totals
        ]);
    }
}
