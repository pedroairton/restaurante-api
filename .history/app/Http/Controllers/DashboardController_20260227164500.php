<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
        ]);
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
