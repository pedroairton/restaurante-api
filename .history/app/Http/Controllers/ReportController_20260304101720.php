<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(ReportRequest $request) {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        
    }

    private function getTotalRevenue(Carbon $start, Carbon $end) {
        return (float) Order::paid()->inPeriod($start,$end)->sum('total');
    }
    private function getTotalOrders(Carbon $start, Carbon $end) {
        return Order::paid()->inPeriod($start,$end)->count();
    }

    private function getSalesByCategory(Carbon $start, Carbon $end) {
        return OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(order_items.quantity as total_quantity'),
                DB::raw('ROUND(SUM(order_items.subtotal) / (SELECT SUM(oi2.subtotal) 
                FROM order_items oi2
                JOIN orders o2 ON oi2.order_id = o2.id
                WHERE o2.status = "paid"
                AND o2.created_at BETWEEN ? AND ?) * 100, 2) as percentage')
            )
            ->addBinding([$start, $end])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }
}
