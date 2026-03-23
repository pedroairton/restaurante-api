<?php
// app/Http/Controllers/Api/ReportController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(ReportRequest $request)
    {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        $revenue      = $this->getTotalRevenue($startDate, $endDate);
        $totalOrders  = $this->getTotalOrders($startDate, $endDate);
        $avgTicket    = $totalOrders > 0 ? $revenue / $totalOrders : 0;

        return response()->json([
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
            ],
            'total_revenue'        => round($revenue, 2),
            'total_orders'         => $totalOrders,
            'average_ticket'       => round($avgTicket, 2),
            'sales_by_category'    => $this->getSalesByCategory($startDate, $endDate),
            'top_products'         => $this->getTopProducts($startDate, $endDate),
            'daily_revenue'        => $this->getDailyRevenue($startDate, $endDate),
        ]);
    }

    // public function exportPdf(ReportRequest $request)
    // {
    //     $startDate = Carbon::parse($request->start_date)->startOfDay();
    //     $endDate   = Carbon::parse($request->end_date)->endOfDay();

    //     $revenue     = $this->getTotalRevenue($startDate, $endDate);
    //     $totalOrders = $this->getTotalOrders($startDate, $endDate);
    //     $avgTicket   = $totalOrders > 0 ? $revenue / $totalOrders : 0;

    //     $data = [
    //         'period' => [
    //             'start' => $startDate->format('d/m/Y'),
    //             'end'   => $endDate->format('d/m/Y'),
    //         ],
    //         'total_revenue'     => $revenue,
    //         'total_orders'      => $totalOrders,
    //         'average_ticket'    => $avgTicket,
    //         'sales_by_category' => $this->getSalesByCategory($startDate, $endDate),
    //         'top_products'      => $this->getTopProducts($startDate, $endDate),
    //     ];

    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.monthly', $data);

    //     return $pdf->download("relatorio_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.pdf");
    // }

    private function getTotalRevenue(Carbon $start, Carbon $end): float
    {
        return (float) Order::paid()
            ->inPeriod($start, $end)
            ->sum('total');
    }

    private function getTotalOrders(Carbon $start, Carbon $end): int
    {
        return Order::paid()
            ->inPeriod($start, $end)
            ->count();
    }

    private function getSalesByCategory(Carbon $start, Carbon $end): array
    {
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
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('ROUND(SUM(order_items.subtotal) / (SELECT SUM(oi2.subtotal) 
                    FROM order_items oi2 
                    JOIN orders o2 ON oi2.order_id = o2.id 
                    WHERE o2.status = "paid" 
                    AND o2.created_at BETWEEN ? AND ?) * 100, 2) as percentage')
            )
            ->addBinding([$start, $end], 'select')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }

    private function getTopProducts(Carbon $start, Carbon $end, int $limit = 10): array
    {
        return OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'products.id',
                'products.name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function getDailyRevenue(Carbon $start, Carbon $end): array
    {
        $sales = Order::paid()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as daily_total'),
                DB::raw('COUNT(*) as daily_orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            $daySale = $sales->get($dateStr);

            $result[] = [
                'date'         => $dateStr,
                'daily_total'  => $daySale ? (float) $daySale->daily_total : 0,
                'daily_orders' => $daySale ? (int) $daySale->daily_orders : 0,
            ];

            $current->addDay();
        }

        return $result;
    }
}