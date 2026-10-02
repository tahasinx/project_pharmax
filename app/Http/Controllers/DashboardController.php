<?php

namespace App\Http\Controllers;

use App\Domain\Organization\BranchContext;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalLine;
use App\Models\LedgerAccount;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Stock;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today    = now()->toDateString();
        $branchId = BranchContext::id();
        $seesAll  = BranchContext::seesAll();

        $saleQuery     = Invoice::query()->when(! $seesAll && $branchId, fn ($q) => $q->where('branch_id', $branchId));
        $purchaseQuery = Purchase::query()->when(! $seesAll && $branchId, fn ($q) => $q->where('branch_id', $branchId));
        $stockQuery    = Stock::query()->when(! $seesAll && $branchId, function ($q) use ($branchId) {
            $q->where(function ($inner) use ($branchId) {
                $inner->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        });

        $todaysSales     = (float) (clone $saleQuery)->whereDate('date', $today)->sum('total_amount');
        $todaysPurchases = (float) (clone $purchaseQuery)->whereDate('purchase_date', $today)->sum('grand_total');
        $invoiceCount    = (clone $saleQuery)->whereDate('date', $today)->count();
        $todaysCost      = (float) InvoiceItem::query()
            ->whereHas('invoice', function ($q) use ($today, $seesAll, $branchId) {
                $q->whereDate('date', $today);
                if (! $seesAll && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->sum('cost_amount');
        $gross = $todaysSales - $todaysCost;

        $available = (clone $stockQuery)->where('quantity', '>', 0)->where(function ($q) {
            $q->whereNull('status')->orWhere('status', 'available');
        });
        $inventoryValue = (float) (clone $available)->selectRaw('COALESCE(SUM(quantity * purchase_price), 0) as value')->value('value');
        $mrpValue       = (float) (clone $available)->selectRaw('COALESCE(SUM(quantity * COALESCE(mrp, selling_price, 0)), 0) as value')->value('value');

        $cashId  = LedgerAccount::where('code', '1000')->value('id');
        $arId    = LedgerAccount::where('code', '1100')->value('id');
        $apId    = LedgerAccount::where('code', '2000')->value('id');
        $balance = function (?int $accountId, bool $debitNormal) use ($seesAll, $branchId) {
            if (! $accountId) {
                return 0.0;
            }
            $lines = JournalLine::where('ledger_account_id', $accountId)
                ->when(! $seesAll && $branchId, fn ($q) => $q->where('branch_id', $branchId));
            $net = (float) $lines->sum('debit') - (float) $lines->sum('credit');

            return round($debitNormal ? $net : -$net, 2);
        };

        $since30 = now()->subDays(30);
        $fast    = InvoiceItem::query()
            ->selectRaw('medicine_id, SUM(quantity) as sold')
            ->whereHas('invoice', fn ($q) => $q->where('date', '>=', $since30))
            ->groupBy('medicine_id')
            ->orderByDesc('sold')
            ->limit(10)
            ->get();
        $names = Medicine::whereIn('id', $fast->pluck('medicine_id'))->pluck('name', 'id');

        $deadDays    = (int) (Setting::query()->value('dead_stock_days') ?: 90);
        $sinceWindow = now()->subDays($deadDays);

        $soldInWindow = InvoiceItem::query()
            ->selectRaw('medicine_id, SUM(quantity) as sold')
            ->whereHas('invoice', fn ($q) => $q->where('date', '>=', $sinceWindow))
            ->groupBy('medicine_id')
            ->get();
        $soldIds = $soldInWindow->pluck('medicine_id');
        $dead    = Medicine::query()
            ->whereHas('stocks', fn ($q) => $q->where('quantity', '>', 0))
            ->whereNotIn('id', $soldIds)
            ->limit(10)
            ->get();
        $slowIds    = $soldInWindow->sortBy('sold')->take(10)->pluck('medicine_id');
        $slowNames  = Medicine::whereIn('id', $slowIds)->pluck('name', 'id');
        $slowMovers = $soldInWindow->sortBy('sold')->take(10)->map(fn ($row) => [
            'name' => $slowNames[$row->medicine_id] ?? 'Medicine',
            'sold' => (int) $row->sold,
        ])->values();

        $low = (clone $available)->whereColumn('quantity', '<=', 'min_stock_level')->count();
        $out = Medicine::where('status', true)
            ->whereDoesntHave('stocks', fn ($q) => $q->where('quantity', '>', 0)->where(function ($s) {
                $s->whereNull('status')->orWhere('status', 'available');
            }))
            ->count();

        $expired  = (clone $stockQuery)->where('quantity', '>', 0)->whereDate('expiry_date', '<', $today)->count();
        $expiring = (clone $stockQuery)->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [$today, now()->addDays(30)->toDateString()])
            ->count();

        $profitByCategory = InvoiceItem::query()
            ->selectRaw('categories.name as category, SUM(invoice_items.total_amount - invoice_items.cost_amount) as profit')
            ->join('medicines', 'medicines.id', '=', 'invoice_items.medicine_id')
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->groupBy('categories.name')
            ->orderByDesc('profit')
            ->limit(8)
            ->get();

        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date          = now()->subMonths($i);
            $monthlyData[] = [
                'month'     => $date->format('M Y'),
                'sales'     => (float) (clone $saleQuery)->whereYear('date', $date->year)->whereMonth('date', $date->month)->sum('total_amount'),
                'purchases' => (float) (clone $purchaseQuery)->whereYear('purchase_date', $date->year)->whereMonth('purchase_date', $date->month)->sum('grand_total'),
            ];
        }

        $fromDay    = now()->subDays(29)->toDateString();
        $salesByDay = (clone $saleQuery)
            ->where('date', '>=', $fromDay)
            ->selectRaw('date as day, SUM(total_amount) as total')
            ->groupBy('date')
            ->get()
            ->mapWithKeys(fn ($row) => [Carbon::parse($row->day)->toDateString() => (float) $row->total]);
        $purchasesByDay = (clone $purchaseQuery)
            ->where('purchase_date', '>=', $fromDay)
            ->selectRaw('purchase_date as day, SUM(grand_total) as total')
            ->groupBy('purchase_date')
            ->get()
            ->mapWithKeys(fn ($row) => [Carbon::parse($row->day)->toDateString() => (float) $row->total]);
        $dailyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $day         = now()->subDays($i);
            $key         = $day->toDateString();
            $dailyData[] = [
                'label'     => $day->format('d M'),
                'sales'     => (float) ($salesByDay[$key] ?? 0),
                'purchases' => (float) ($purchasesByDay[$key] ?? 0),
            ];
        }

        $change = function (float $current, float $previous): float {
            if ($previous == 0.0) {
                return $current > 0 ? 100.0 : 0.0;
            }

            return round((($current - $previous) / abs($previous)) * 100, 1);
        };
        $weekStart   = now()->startOfWeek()->toDateString();
        $prevStart   = now()->subWeek()->startOfWeek()->toDateString();
        $prevEnd     = now()->subWeek()->endOfWeek()->toDateString();
        $periodSales = function (string $start, string $end) use ($saleQuery) {
            return (float) (clone $saleQuery)->whereBetween('date', [$start, $end])->sum('total_amount');
        };
        $periodPurchases = function (string $start, string $end) use ($purchaseQuery) {
            return (float) (clone $purchaseQuery)->whereBetween('purchase_date', [$start, $end])->sum('grand_total');
        };
        $weekSales         = $periodSales($weekStart, $today);
        $prevWeekSales     = $periodSales($prevStart, $prevEnd);
        $weekPurchases     = $periodPurchases($weekStart, $today);
        $prevWeekPurchases = $periodPurchases($prevStart, $prevEnd);
        $periodMargin      = function (string $start, string $end) use ($saleQuery, $seesAll, $branchId) {
            $sales = (float) (clone $saleQuery)->whereBetween('date', [$start, $end])->sum('total_amount');
            $cost  = (float) InvoiceItem::query()
                ->whereHas('invoice', function ($q) use ($start, $end, $seesAll, $branchId) {
                    $q->whereBetween('date', [$start, $end]);
                    if (! $seesAll && $branchId) {
                        $q->where('branch_id', $branchId);
                    }
                })
                ->sum('cost_amount');

            return $sales > 0 ? (($sales - $cost) / $sales) * 100 : 0.0;
        };

        $recentSales = (clone $saleQuery)
            ->with('customer:id,name')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id'     => 's-'.$invoice->id,
                'kind'   => 'sale',
                'title'  => $invoice->invoice_no ?: 'Sale',
                'party'  => $invoice->customer?->name ?: 'Walk-in',
                'date'   => optional($invoice->date)->format('d M, Y'),
                'amount' => (float) $invoice->total_amount,
                'due'    => (float) $invoice->due_amount,
                'href'   => route('invoices.show', $invoice),
            ]);
        $recentPurchases = (clone $purchaseQuery)
            ->with('manufacturer:id,name')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id'     => 'p-'.$purchase->id,
                'kind'   => 'purchase',
                'title'  => $purchase->purchase_no ?: 'Purchase',
                'party'  => $purchase->manufacturer?->name ?: 'Supplier',
                'date'   => optional($purchase->purchase_date)->format('d M, Y'),
                'amount' => (float) $purchase->grand_total,
                'due'    => (float) $purchase->due_amount,
                'href'   => route('purchases.show', $purchase),
            ]);

        $payments = (clone $saleQuery)
            ->selectRaw("COALESCE(NULLIF(payment_type, ''), 'Unspecified') as method, SUM(total_amount) as total")
            ->groupByRaw("COALESCE(NULLIF(payment_type, ''), 'Unspecified')")
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'method' => $row->method,
                'total'  => round((float) $row->total, 2),
            ]);

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'todays_sales'        => round($todaysSales, 2),
                'todays_purchases'    => round($todaysPurchases, 2),
                'gross_profit'        => round($gross, 2),
                'gross_margin'        => $todaysSales > 0 ? round(($gross / $todaysSales) * 100, 1) : 0,
                'net_profit'          => round($gross, 2),
                'cash_in_hand'        => $balance($cashId, true),
                'receivables'         => round((float) (clone $saleQuery)->sum('due_amount'), 2) ?: $balance($arId, true),
                'payables'            => round((float) (clone $purchaseQuery)->sum('due_amount'), 2) ?: $balance($apId, false),
                'inventory_value'     => round($inventoryValue, 2),
                'mrp_value'           => round($mrpValue, 2),
                'expiring'            => $expiring,
                'expired'             => $expired,
                'low_stock'           => $low,
                'out_of_stock'        => $out,
                'basket'              => $invoiceCount > 0 ? round($todaysSales / $invoiceCount, 2) : 0,
                'prescriptions_today' => Prescription::query()
                    ->whereDate('created_at', $today)
                    ->when(! $seesAll && $branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->count(),
                'dispensed_today' => Prescription::query()
                    ->where('status', 'dispensed')
                    ->whereDate('updated_at', $today)
                    ->when(! $seesAll && $branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->count(),
                'sales_change'     => $change($weekSales, $prevWeekSales),
                'purchases_change' => $change($weekPurchases, $prevWeekPurchases),
                'margin_change'    => $change($periodMargin($weekStart, $today), $periodMargin($prevStart, $prevEnd)),
            ],
            'dailyData'  => $dailyData,
            'activity'   => $recentSales->concat($recentPurchases)->values(),
            'payments'   => $payments,
            'fastMovers' => $fast->map(fn ($row) => [
                'name' => $names[$row->medicine_id] ?? 'Medicine',
                'sold' => (int) $row->sold,
            ]),
            'deadStock'        => $dead,
            'slowMovers'       => $slowMovers,
            'deadStockDays'    => $deadDays,
            'profitByCategory' => $profitByCategory,
            'monthlyData'      => $monthlyData,
        ]);
    }
}
