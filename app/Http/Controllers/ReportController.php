<?php

namespace App\Http\Controllers;

use App\Domain\Organization\BranchContext;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        $from = now()->startOfMonth();
        $to = now()->endOfDay();

        $sales = (float) BranchContext::constrain(
            Invoice::query()->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)
        )->sum('total_amount');

        $purchases = (float) BranchContext::constrain(
            Purchase::query()->whereDate('purchase_date', '>=', $from)->whereDate('purchase_date', '<=', $to)
        )->sum('grand_total');

        $dues = (float) BranchContext::constrain(
            Invoice::query()->where('due_amount', '>', 0)
        )->sum('due_amount');

        $stockValue = (float) BranchContext::constrainStock(
            Stock::query()->where('quantity', '>', 0)->with('medicine:id,manufacturer_price,price')
        )
            ->get()
            ->sum(function (Stock $stock) {
                $cost = (float) ($stock->medicine?->manufacturer_price ?? $stock->purchase_price ?? 0);

                return $stock->quantity * $cost;
            });

        return Inertia::render('Reports/Index', [
            'hub' => [
                'sales' => $sales,
                'purchases' => $purchases,
                'gross_profit' => $sales - $purchases,
                'dues' => $dues,
                'stock_value' => $stockValue,
                'period_label' => $from->format('M Y'),
            ],
            'links' => $this->reportLinks(),
        ]);
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = $this->scopeInvoiceJoin(
            InvoiceItem::query()
                ->select([
                    'invoice_items.*',
                    'invoices.date as invoice_date',
                    'invoices.customer_id',
                    'invoices.user_id',
                    'invoices.payment_type',
                ])
                ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->when($from, fn ($q) => $q->whereDate('invoices.date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('invoices.date', '<=', $to))
        );

        if ($medicineId = $request->integer('medicine_id')) {
            $query->where('invoice_items.medicine_id', $medicineId);
        }
        if ($customerId = $request->integer('customer_id')) {
            $query->where('invoices.customer_id', $customerId);
        }

        $items = $query->with(['medicine:id,name', 'invoice:id,customer_id,date,user_id,payment_type'])
            ->orderByDesc('invoices.date')
            ->get();

        $summary = [
            'total_quantity' => (int) $items->sum('quantity'),
            'total_sales' => (float) $items->sum('total_amount'),
        ];

        $daily = $items->groupBy(fn ($i) => optional($i->invoice)->date?->toDateString())
            ->map(fn ($g) => [
                'quantity' => (int) $g->sum('quantity'),
                'sales' => (float) $g->sum('total_amount'),
            ])
            ->filter(fn ($v, $k) => ! is_null($k));

        $monthly = $items->groupBy(function ($i) {
            $d = optional($i->invoice)->date;

            return $d ? $d->format('Y-m') : null;
        })->map(fn ($g) => [
            'quantity' => (int) $g->sum('quantity'),
            'sales' => (float) $g->sum('total_amount'),
        ])->filter(fn ($v, $k) => ! is_null($k));

        $byPayment = $items->groupBy(fn ($i) => $i->payment_type ?: 'unknown')
            ->map(fn ($g, $type) => [
                'type' => $type,
                'sales' => (float) $g->sum('total_amount'),
                'quantity' => (int) $g->sum('quantity'),
            ])->values();

        $userNames = User::whereIn('id', $items->pluck('user_id')->filter()->unique())->pluck('name', 'id');
        $byCashier = $items->groupBy(fn ($i) => $i->user_id ?: 0)
            ->map(fn ($g, $uid) => [
                'cashier' => $uid ? ($userNames[$uid] ?? 'User #'.$uid) : 'Unassigned',
                'sales' => (float) $g->sum('total_amount'),
                'quantity' => (int) $g->sum('quantity'),
            ])->values();

        if ($request->boolean('export')) {
            return $this->csv('sales-report.csv', ['Date', 'Medicine', 'Qty', 'Sales', 'Payment', 'Cashier'], $items->map(fn ($i) => [
                optional($i->invoice)->date?->toDateString(),
                $i->medicine?->name,
                $i->quantity,
                $i->total_amount,
                $i->payment_type,
                $i->user_id ? ($userNames[$i->user_id] ?? '') : '',
            ]));
        }

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
            'byPayment' => $byPayment,
            'byCashier' => $byCashier,
        ]);
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $manufacturerId = $request->integer('manufacturer_id');

        $purchases = $this->scopeBranch(
            Purchase::query()
                ->when($from, fn ($q) => $q->whereDate('purchase_date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('purchase_date', '<=', $to))
                ->when($manufacturerId, fn ($q) => $q->where('manufacturer_id', $manufacturerId))
                ->with('manufacturer:id,name')
                ->orderByDesc('purchase_date')
        )->get();

        $summary = [
            'count' => $purchases->count(),
            'total' => (float) $purchases->sum('grand_total'),
            'tax' => (float) $purchases->sum('total_tax'),
            'discount' => (float) $purchases->sum('total_discount'),
        ];

        $bySupplier = $purchases->groupBy(fn ($p) => optional($p->manufacturer)->name ?? 'Unknown')
            ->map(fn ($g, $name) => [
                'name' => $name,
                'count' => $g->count(),
                'total' => (float) $g->sum('grand_total'),
            ])->values();

        if ($request->boolean('export')) {
            return $this->csv('purchases-report.csv', ['Date', 'Purchase #', 'Manufacturer', 'Total', 'Tax'], $purchases->map(fn ($p) => [
                optional($p->purchase_date)?->toDateString() ?? $p->purchase_date,
                $p->purchase_no,
                $p->manufacturer?->name,
                $p->grand_total,
                $p->total_tax,
            ]));
        }

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
        [$from, $to] = $this->dateRange($request);

        $items = $this->scopeInvoiceJoin(
            InvoiceItem::query()
                ->select(['invoice_items.*', 'invoices.date'])
                ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->when($from, fn ($q) => $q->whereDate('invoices.date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('invoices.date', '<=', $to))
                ->with('medicine:id,name,manufacturer_price')
        )->get();

        $revenue = (float) $items->sum('total_amount');
        $avgCostByMedicine = Medicine::whereIn('id', $items->pluck('medicine_id')->unique()->values())
            ->get()
            ->mapWithKeys(fn ($m) => [$m->id => (float) ($m->average_purchase_price ?? $m->manufacturer_price ?? 0)]);

        $cogs = 0.0;
        foreach ($items as $item) {
            $cogs += ((int) $item->quantity) * ($avgCostByMedicine[$item->medicine_id] ?? 0.0);
        }

        $metrics = [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $revenue - $cogs,
        ];

        if ($request->boolean('export')) {
            return $this->csv('profit-loss.csv', ['Metric', 'Amount'], collect([
                ['Revenue', $metrics['revenue']],
                ['COGS', $metrics['cogs']],
                ['Gross profit', $metrics['gross_profit']],
            ]));
        }

        return Inertia::render('Reports/ProfitLoss', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'metrics' => $metrics,
        ]);
    }

    public function customerDues(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $invoices = $this->scopeBranch(
            Invoice::query()
                ->with('customer:id,name')
                ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
                ->where('due_amount', '>', 0)
        )->get(['id', 'customer_id', 'date', 'due_amount', 'invoice_no']);

        $byCustomer = $invoices->groupBy(fn ($i) => optional($i->customer)->name ?? 'Unknown')
            ->map(fn ($g, $name) => [
                'name' => $name,
                'invoices' => $g->count(),
                'due' => (float) $g->sum('due_amount'),
            ])
            ->sortByDesc('due')
            ->values();

        if ($request->boolean('export')) {
            return $this->csv('customer-dues.csv', ['Customer', 'Invoices', 'Due'], $byCustomer->map(fn ($r) => [
                $r['name'], $r['invoices'], $r['due'],
            ]));
        }

        return Inertia::render('Reports/CustomerDues', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'byCustomer' => $byCustomer,
        ]);
    }

    public function stockValuation(Request $request)
    {
        $rows = BranchContext::constrainStock(
            Stock::query()
                ->where('quantity', '>', 0)
                ->with('medicine:id,name,manufacturer_price,price')
                ->orderBy('medicine_id')
        )
            ->get()
            ->map(function (Stock $stock) {
                $cost = (float) ($stock->medicine?->manufacturer_price ?? $stock->purchase_price ?? 0);
                $mrp = (float) ($stock->medicine?->price ?? 0);

                return [
                    'medicine' => $stock->medicine?->name ?? '—',
                    'batch' => $stock->batch_number,
                    'quantity' => (int) $stock->quantity,
                    'cost' => $cost,
                    'mrp' => $mrp,
                    'value_cost' => $stock->quantity * $cost,
                    'value_mrp' => $stock->quantity * $mrp,
                    'expiry' => optional($stock->expiry_date)?->toDateString(),
                ];
            });

        $summary = [
            'batches' => $rows->count(),
            'units' => (int) $rows->sum('quantity'),
            'value_cost' => (float) $rows->sum('value_cost'),
            'value_mrp' => (float) $rows->sum('value_mrp'),
        ];

        if ($request->boolean('export')) {
            return $this->csv('stock-valuation.csv', ['Medicine', 'Batch', 'Qty', 'Cost', 'MRP', 'Value cost', 'Value MRP', 'Expiry'], $rows->map(fn ($r) => [
                $r['medicine'], $r['batch'], $r['quantity'], $r['cost'], $r['mrp'], $r['value_cost'], $r['value_mrp'], $r['expiry'],
            ]));
        }

        return Inertia::render('Reports/StockValuation', [
            'summary' => $summary,
            'rows' => $rows->values(),
        ]);
    }

    public function expiryAging()
    {
        $stocks = BranchContext::constrainStock(
            Stock::query()
                ->where('quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->with('medicine:id,name')
        )->get();

        $buckets = [
            'expired' => [],
            'd0_30' => [],
            'd31_60' => [],
            'd61_90' => [],
            'd91_180' => [],
            'd180' => [],
        ];

        foreach ($stocks as $stock) {
            $days = now()->startOfDay()->diffInDays($stock->expiry_date->startOfDay(), false);
            $row = [
                'medicine' => $stock->medicine?->name ?? '—',
                'batch' => $stock->batch_number,
                'quantity' => (int) $stock->quantity,
                'expiry' => $stock->expiry_date->toDateString(),
                'days' => (int) $days,
            ];
            if ($days < 0) {
                $buckets['expired'][] = $row;
            } elseif ($days <= 30) {
                $buckets['d0_30'][] = $row;
            } elseif ($days <= 60) {
                $buckets['d31_60'][] = $row;
            } elseif ($days <= 90) {
                $buckets['d61_90'][] = $row;
            } elseif ($days <= 180) {
                $buckets['d91_180'][] = $row;
            } else {
                $buckets['d180'][] = $row;
            }
        }

        $summary = collect($buckets)->map(fn ($rows, $key) => [
            'key' => $key,
            'count' => count($rows),
            'units' => collect($rows)->sum('quantity'),
        ])->values();

        return Inertia::render('Reports/ExpiryAging', [
            'summary' => $summary,
            'buckets' => $buckets,
            'labels' => [
                'expired' => 'Expired',
                'd0_30' => '0–30 days',
                'd31_60' => '31–60 days',
                'd61_90' => '61–90 days',
                'd91_180' => '91–180 days',
                'd180' => '180+ days',
            ],
        ]);
    }

    public function movers(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = $this->scopeInvoiceJoin(
            InvoiceItem::query()
                ->selectRaw('medicine_id, SUM(quantity) as qty, SUM(total_amount) as sales')
                ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->when($from, fn ($q) => $q->whereDate('invoices.date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('invoices.date', '<=', $to))
                ->groupBy('medicine_id')
                ->orderByDesc('qty')
        )->get();

        $names = Medicine::whereIn('id', $rows->pluck('medicine_id'))->pluck('name', 'id');
        $mapped = $rows->map(fn ($r) => [
            'medicine' => $names[$r->medicine_id] ?? '—',
            'quantity' => (int) $r->qty,
            'sales' => (float) $r->sales,
        ]);

        $fast = $mapped->take(20)->values();
        $slow = $mapped->sortBy('quantity')->take(20)->values();

        if ($request->boolean('export')) {
            return $this->csv('movers.csv', ['Type', 'Medicine', 'Qty', 'Sales'], $fast->map(fn ($r) => ['fast', $r['medicine'], $r['quantity'], $r['sales']])
                ->concat($slow->map(fn ($r) => ['slow', $r['medicine'], $r['quantity'], $r['sales']])));
        }

        return Inertia::render('Reports/Movers', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'fast' => $fast,
            'slow' => $slow,
        ]);
    }

    public function paymentMix(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $invoices = $this->scopeBranch(
            Invoice::query()
                ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
        )->get(['id', 'payment_type', 'total_amount', 'paid_amount']);

        $rows = $invoices->groupBy(fn ($i) => $i->payment_type ?: 'unknown')
            ->map(fn ($g, $type) => [
                'type' => $type,
                'count' => $g->count(),
                'total' => (float) $g->sum('total_amount'),
                'paid' => (float) $g->sum('paid_amount'),
            ])->values();

        $summary = [
            'invoices' => $invoices->count(),
            'total' => (float) $invoices->sum('total_amount'),
            'paid' => (float) $invoices->sum('paid_amount'),
        ];

        if ($request->boolean('export')) {
            return $this->csv('payment-mix.csv', ['Type', 'Count', 'Total', 'Paid'], $rows->map(fn ($r) => [
                $r['type'], $r['count'], $r['total'], $r['paid'],
            ]));
        }

        return Inertia::render('Reports/PaymentMix', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    public function taxSummary(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $invoices = $this->scopeBranch(
            Invoice::query()
                ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
                ->orderBy('date')
        )->get(['id', 'date', 'invoice_no', 'total_amount', 'total_tax', 'total_discount']);

        $summary = [
            'invoices' => $invoices->count(),
            'taxable' => (float) $invoices->sum('total_amount'),
            'tax' => (float) $invoices->sum('total_tax'),
            'discount' => (float) $invoices->sum('total_discount'),
        ];

        $daily = $invoices->groupBy(fn ($i) => optional($i->date)?->toDateString())
            ->map(fn ($g, $date) => [
                'date' => $date,
                'tax' => (float) $g->sum('total_tax'),
                'sales' => (float) $g->sum('total_amount'),
            ])->values();

        if ($request->boolean('export')) {
            return $this->csv('tax-summary.csv', ['Date', 'Invoice', 'Sales', 'Tax', 'Discount'], $invoices->map(fn ($i) => [
                optional($i->date)?->toDateString(), $i->invoice_no, $i->total_amount, $i->total_tax, $i->total_discount,
            ]));
        }

        return Inertia::render('Reports/TaxSummary', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'summary' => $summary,
            'daily' => $daily,
            'rows' => $invoices->take(200)->values(),
        ]);
    }

    public function cashierSales(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $invoices = $this->scopeBranch(
            Invoice::query()
                ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
                ->with('user:id,name')
        )->get(['id', 'user_id', 'total_amount', 'paid_amount', 'due_amount', 'total_tax']);

        $rows = $invoices->groupBy(fn ($i) => $i->user_id ?: 0)
            ->map(fn ($g, $uid) => [
                'cashier' => $uid ? (optional($g->first()->user)->name ?? 'User #'.$uid) : 'Unassigned',
                'invoices' => $g->count(),
                'sales' => (float) $g->sum('total_amount'),
                'paid' => (float) $g->sum('paid_amount'),
                'due' => (float) $g->sum('due_amount'),
                'tax' => (float) $g->sum('total_tax'),
            ])
            ->sortByDesc('sales')
            ->values();

        if ($request->boolean('export')) {
            return $this->csv('cashier-sales.csv', ['Cashier', 'Invoices', 'Sales', 'Paid', 'Due', 'Tax'], $rows->map(fn ($r) => [
                $r['cashier'], $r['invoices'], $r['sales'], $r['paid'], $r['due'], $r['tax'],
            ]));
        }

        return Inertia::render('Reports/CashierSales', [
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'rows' => $rows,
            'summary' => [
                'cashiers' => $rows->count(),
                'sales' => (float) $rows->sum('sales'),
                'invoices' => (int) $rows->sum('invoices'),
            ],
        ]);
    }

    private function dateRange(Request $request): array
    {
        return [$request->date('from'), $request->date('to')];
    }

    private function scopeBranch(Builder $query, string $column = 'branch_id'): Builder
    {
        return BranchContext::constrain($query, $column);
    }

    private function scopeInvoiceJoin(Builder $query): Builder
    {
        return BranchContext::constrain($query, 'invoices.branch_id');
    }

    private function csv(string $filename, array $headers, $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, is_array($row) ? $row : (method_exists($row, 'toArray') ? array_values($row->toArray()) : (array) $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function reportLinks(): array
    {
        return [
            ['title' => 'Sales', 'route' => 'reports.sales', 'desc' => 'Quantity, revenue, payment and cashier mix'],
            ['title' => 'Purchases', 'route' => 'reports.purchases', 'desc' => 'Supplier spend, tax and discounts'],
            ['title' => 'Profit & loss', 'route' => 'reports.profit-loss', 'desc' => 'Revenue, COGS and gross margin'],
            ['title' => 'Customer dues', 'route' => 'reports.customer-dues', 'desc' => 'Outstanding receivables'],
            ['title' => 'Stock valuation', 'route' => 'reports.stock-valuation', 'desc' => 'On-hand value at cost and MRP'],
            ['title' => 'Expiry aging', 'route' => 'reports.expiry-aging', 'desc' => 'Batches by days to expiry'],
            ['title' => 'Fast / slow movers', 'route' => 'reports.movers', 'desc' => 'Top and bottom sellers'],
            ['title' => 'Payment mix', 'route' => 'reports.payment-mix', 'desc' => 'Cash, card, mobile and credit'],
            ['title' => 'Tax summary', 'route' => 'reports.tax-summary', 'desc' => 'Tax collected by period'],
            ['title' => 'Cashier sales', 'route' => 'reports.cashier-sales', 'desc' => 'Sales by counter user'],
        ];
    }
}
