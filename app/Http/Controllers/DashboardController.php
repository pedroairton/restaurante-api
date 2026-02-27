<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        return response()->json([
            'monthly_revenue' => $this->getMonthlyRevenue($month, $year),
            'monthly_orders' => $this->getMonthlyOrders($month, $year),
            'total_products' => Product::active()->count(),
            'weekly_sales' => $this->getWeeklySales(),
        ]);
    }

    public function topProducts(Request $request){
        $period = $request->get('period', 'total');

        $query = OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->select(
                'products.id',
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10);

            if($period === 'week') {
                $query->whereBetween('orders.created_at', [
                    Carbon::now()->startOfWeek(), 
                    Carbon::now()->endOfWeek()
                ]);
            }

            return response()->json($query->get());
    }

    private function getMonthlyRevenue(int $month, int $year)
    {
        return (float) Order::paid()->inMonth($month, $year)->sum('total');
    }
    private function getMonthlyOrders(int $month, int $year)
    {
        return (int) Order::notCancelled()->inMonth($month, $year)->count();
    }
    private function getWeeklySales(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $sales = Order::paid()->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as daily_total'),
                DB::raw('COUNT(*) as daily_orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $result = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
            $daySale = $sales->firstWhere('date', $date);

            $result[] = [
                'date' => $date,
                'day_name' => Carbon::parse($date)->locale('pt-BR')->dayName,
                'daily_total' => $daySale ? $daySale->daily_total : 0,
                'daily_orders' => $daySale ? $daySale->daily_orders : 0,
            ];
        }
        return $result;
    }
}
