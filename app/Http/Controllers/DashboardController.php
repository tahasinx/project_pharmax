<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');

        // Get dashboard statistics
        $stats = [
            'total_customers'    => Customer::count(),
            'total_medicines'    => Medicine::count(),
            'out_of_stock'       => Medicine::where('status', true)->count(), // This would need stock calculation
            'expired_medicines'  => 0, // This would need expiry date calculation
            'todays_sales'       => Invoice::whereDate('date', $today)->sum('total_amount'),
            'todays_purchases'   => Purchase::whereDate('purchase_date', $today)->sum('grand_total'),
        ];

        // Best selling products by total quantity sold (not count of rows)
        $bestSellingProducts = Medicine::with(['category'])
            ->whereHas('invoiceItems')
            ->withSum('invoiceItems as sold_quantity', 'quantity')
            ->with(['stocks' => function ($q) {
                $q->active()->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
            }])
            ->orderByDesc('sold_quantity')
            ->take(12)
            ->get()
            ->map(function ($medicine) {
                $batch = $medicine->stocks->first();
                $medicine->display_price = $batch && $batch->selling_price !== null
                    ? (float) $batch->selling_price
                    : (float) $medicine->price;
                return $medicine->only([
                    'id',
                    'name',
                    'display_price',
                    'sold_quantity'
                ]) + [
                    'category' => $medicine->category ? $medicine->category->only(['id', 'name']) : null,
                ];
            });

        // Get monthly sales data for chart
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyData[] = [
                'month'     => $date->format('M Y'),
                'sales'     => Invoice::whereYear('date', $date->year)
                    ->whereMonth('date', $date->month)
                    ->sum('total_amount'),
                'purchases' => Purchase::whereYear('purchase_date', $date->year)
                    ->whereMonth('purchase_date', $date->month)
                    ->sum('grand_total'),
            ];
        }

        return Inertia::render('Dashboard/Index', [
            'stats'               => $stats,
            'bestSellingProducts' => $bestSellingProducts,
            'monthlyData'         => $monthlyData,
        ]);
    }
}
