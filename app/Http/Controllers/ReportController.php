<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view-reports']);
    }

    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function sales(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');
        $medicineId = $request->integer('medicine_id');
        $customerId = $request->integer('customer_id');

        $query = InvoiceItem::query()
            ->select([
                'invoice_items.*',
                'invoices.date as invoice_date',
                'invoices.customer_id',
            ])
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id');

        if ($from) {
            $query->whereDate('invoices.date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('invoices.date', '<=', $to);
        }
        if ($medicineId) {
            $query->where('invoice_items.medicine_id', $medicineId);
        }
        if ($customerId) {
            $query->where('invoices.customer_id', $customerId);
        }

        $items = $query->with(['medicine:id,name', 'invoice:id,customer_id,date'])
            ->orderByDesc('invoices.date')
            ->get();

        $summary = [
            'total_quantity' => (int) $items->sum('quantity'),
            'total_sales' => (float) $items->sum('total_amount'),
        ];

        $daily = $items->groupBy(fn($i) => optional($i->invoice)->date?->toDateString())
            ->map(fn($g) => [
                'quantity' => (int) $g->sum('quantity'),
                'sales' => (float) $g->sum('total_amount'),
            ])
            ->filter(fn($v, $k) => !is_null($k));

        $monthly = $items->groupBy(function ($i) {
            $d = optional($i->invoice)->date;
            return $d ? $d->format('Y-m') : null;
        })->map(fn($g) => [
            'quantity' => (int) $g->sum('quantity'),
            'sales' => (float) $g->sum('total_amount'),
        ])->filter(fn($v, $k) => !is_null($k));

        return Inertia::render('Reports/Sales', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'medicine_id' => $medicineId ?: null,
                'customer_id' => $customerId ?: null,
            ],
            'summary' => $summary,
            'daily' => $daily->toArray(),
            'monthly' => $monthly->toArray(),
        ]);
    }

    public function purchases(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');
        $manufacturerId = $request->integer('manufacturer_id');

        $query = Purchase::query();

        if ($from) {
            $query->whereDate('purchase_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('purchase_date', '<=', $to);
        }
        if ($manufacturerId) {
            $query->where('manufacturer_id', $manufacturerId);
        }

        $purchases = $query->with('manufacturer:id,name')
            ->orderByDesc('purchase_date')
            ->get();

        $summary = [
            'count' => $purchases->count(),
            'total' => (float) $purchases->sum('grand_total'),
            'tax' => (float) $purchases->sum('total_tax'),
            'discount' => (float) $purchases->sum('total_discount'),
        ];

        $bySupplier = $purchases->groupBy(fn($p) => optional($p->manufacturer)->name ?? 'Unknown')
            ->map(fn($g, $name) => [
                'name' => $name,
                'count' => $g->count(),
                'total' => (float) $g->sum('grand_total'),
            ])->values();

        return Inertia::render('Reports/Purchases', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'manufacturer_id' => $manufacturerId ?: null,
            ],
            'summary' => $summary,
            'bySupplier' => $bySupplier->toArray(),
        ]);
    }

    public function profitLoss(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $itemsQuery = InvoiceItem::query()
            ->select(['invoice_items.*', 'invoices.date'])
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id');

        if ($from) {
            $itemsQuery->whereDate('invoices.date', '>=', $from);
        }
        if ($to) {
            $itemsQuery->whereDate('invoices.date', '<=', $to);
        }

        $items = $itemsQuery->with('medicine:id,name,manufacturer_price')->get();

        $revenue = (float) $items->sum('total_amount');

        // Approximate COGS: use average purchase price per medicine (fallback to manufacturer_price)
        $avgCostByMedicine = Medicine::whereIn('id', $items->pluck('medicine_id')->unique()->values())
            ->get()
            ->mapWithKeys(function ($m) {
                return [$m->id => (float) ($m->average_purchase_price ?? $m->manufacturer_price ?? 0)];
            });

        $cogs = 0.0;
        foreach ($items as $item) {
            $cost = $avgCostByMedicine[$item->medicine_id] ?? 0.0;
            $cogs += ((int) $item->quantity) * $cost;
        }

        $grossProfit = $revenue - $cogs;

        return Inertia::render('Reports/ProfitLoss', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'metrics' => [
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
            ],
        ]);
    }

    public function customerDues(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $invoices = Invoice::query()
            ->with('customer:id,name')
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            ->where('due_amount', '>', 0)
            ->get(['id', 'customer_id', 'date', 'due_amount']);

        $byCustomer = $invoices->groupBy(fn($i) => optional($i->customer)->name ?? 'Unknown')
            ->map(fn($g, $name) => [
                'name' => $name,
                'invoices' => $g->count(),
                'due' => (float) $g->sum('due_amount'),
            ])
            ->sortByDesc('due')
            ->values();

        return Inertia::render('Reports/CustomerDues', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'byCustomer' => $byCustomer,
        ]);
    }
}
