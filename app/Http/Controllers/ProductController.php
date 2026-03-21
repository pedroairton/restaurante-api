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
        $startDate = Carbon::now()->subWeeks(3)->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        $weeklyRaw = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEARWEEK(orders.created_at, 1) as week'),
                DB::raw('MIN(DATE(orders.created_at)) as week_start'),
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy(DB::raw('YEARWEEK(orders.created_at, 1)'))
            ->get()
            ->keyBy('week');

        // monta timeline com zeros
        $weeklySales = collect();

        $current = $startDate->copy();

        while ($current <= $endDate) {
            $weekKey = $current->format('oW'); // ISO year + week

            $data = $weeklyRaw->get($weekKey);

            $weeklySales->push([
                'week' => $weekKey,
                'week_start' => $current->copy()->startOfWeek()->toDateString(),
                'total_sold' => $data->total_sold ?? 0,
                'total_revenue' => $data->total_revenue ?? 0,
            ]);

            $current->addWeek();
        }

        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $monthlyRaw = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(orders.created_at) as year'),
                DB::raw('MONTH(orders.created_at) as month'),
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn($item) => $item->year . str_pad($item->month, 2, '0', STR_PAD_LEFT));

        // monta timeline com zeros
        $monthlySales = collect();

        $current = $startDate->copy();

        while ($current <= $endDate) {
            $key = $current->format('Ym');

            $data = $monthlyRaw->get($key);

            $monthlySales->push([
                'year' => $current->year,
                'month' => $current->month,
                'total_sold' => $data->total_sold ?? 0,
                'total_revenue' => $data->total_revenue ?? 0,
            ]);

            $current->addMonth();
        }

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
