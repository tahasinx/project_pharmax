<?php

namespace App\Http\Controllers;

use App\Domain\Compliance\ControlledDispenseRecorder;
use App\Domain\Finance\JournalPoster;
use App\Domain\Inventory\FefoAllocator;
use App\Domain\Organization\BranchContext;
use App\Helpers\NotificationHelper;
use App\Mail\InvoiceCreatedMail;
use App\Models\Customer;
use App\Models\HeldBill;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\TenderPayment;
use App\Traits\HasSettingsPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    use HasSettingsPagination;

    public function index()
    {
        $itemsPerPage = $this->getItemsPerPage();
        $invoices     = BranchContext::constrain(
            Invoice::with(['customer', 'user'])->orderBy('created_at', 'desc')
        )->paginate($itemsPerPage)->withQueryString();

        return Inertia::render('Invoice/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function create()
    {
        $customers = Customer::where('status', true)->get();
        $medicines = Medicine::with(['category', 'manufacturer'])
            ->where('status', true)
            ->get();
        $invoiceNo     = $this->generateInvoiceNumber();
        $invoicePrefix = $this->getInvoicePrefix();

        return Inertia::render('Invoice/Create', [
            'customers'     => $customers,
            'medicines'     => $medicines,
            'invoiceNo'     => $invoiceNo,
            'invoicePrefix' => $invoicePrefix,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'         => 'nullable|exists:customers,customer_id',
            'date'                => 'required|date',
            'payment_type'        => 'required|in:cash,bank,credit,card,bkash,nagad,rocket,mixed',
            'paid_amount'         => 'nullable|numeric|min:0',
            'due_amount'          => 'nullable|numeric|min:0',
            'total_amount'        => 'required|numeric|min:0',
            'total_tax'           => 'nullable|numeric|min:0',
            'total_discount'      => 'nullable|numeric|min:0',
            'items'               => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,medicine_id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.rate'        => 'required|numeric|min:0',
            'send_sms'            => 'nullable|boolean',
            'send_email'          => 'nullable|boolean',
            'prescription'        => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:8192',
        ]);

        // Recalculate due on server for accuracy and safety
        $totalAmount = (float) ($request->total_amount ?? 0);
        $paidAmount  = (float) ($request->paid_amount ?? 0);
        $dueAmount   = max(round($totalAmount - $paidAmount, 2), 0);

        $customerId = $request->filled('customer_id')
            ? Customer::localIdOrFail($request->customer_id)
            : Customer::walkIn()->id;

        $prescriptionPath = null;
        $prescriptionName = null;
        if ($request->hasFile('prescription')) {
            $prescriptionPath = $request->file('prescription')->store('prescriptions', 'public');
            $prescriptionName = $request->file('prescription')->getClientOriginalName();
        }

        $invoice = Invoice::create([
            'invoice_id'                  => $this->generateInvoiceId(),
            'branch_id'                   => BranchContext::id(),
            'counter_id'                  => $request->user()?->counter_id,
            'customer_id'                 => $customerId,
            'date'                        => $request->date,
            'invoice_no'                  => $request->invoice_no ?: $this->generateInvoiceNumber(),
            'total_amount'                => $totalAmount,
            'total_tax'                   => $request->total_tax ?? 0,
            'previous_due'                => $request->previous_due ?? 0,
            'paid_amount'                 => $paidAmount,
            'due_amount'                  => $dueAmount,
            'total_discount'              => $request->total_discount ?? 0,
            'invoice_discount'            => $request->invoice_discount ?? 0,
            'user_id'                     => auth()->id(),
            'details'                     => $request->details,
            'payment_type'                => $request->payment_type,
            'prescription_path'           => $prescriptionPath,
            'prescription_original_name'  => $prescriptionName,
        ]);

        $saleCost = 0;
        DB::transaction(function () use ($request, $invoice, &$saleCost) {
            $allocator = app(FefoAllocator::class);
            foreach ($request->items as $item) {
                $medicineLocalId = Medicine::localIdOrFail($item['medicine_id']);
                $result          = $allocator->allocate($medicineLocalId, (int) $item['quantity'], BranchContext::id());
                if ($result['short'] > 0) {
                    $medicine  = Medicine::find($medicineLocalId);
                    $available = $item['quantity'] - $result['short'];
                    $name      = $medicine?->name ?? 'medicine';
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient saleable stock for {$name}. Required: {$item['quantity']}, Available: {$available}"],
                    ]);
                }
                $saleCost += $result['cost'];
                foreach ($result['lines'] as $line) {
                    $stock = $line['stock'];
                    InvoiceItem::create([
                        'invoice_id'   => $invoice->id,
                        'medicine_id'  => $medicineLocalId,
                        'stock_id'     => $stock->id,
                        'batch_id'     => $stock->batch_number ?: (string) $stock->id,
                        'quantity'     => $line['quantity'],
                        'rate'         => $item['rate'],
                        'discount'     => $item['discount'] ?? 0,
                        'cost_amount'  => $line['cost'],
                        'total_amount' => $line['quantity'] * $item['rate'] - ($item['discount'] ?? 0),
                    ]);
                    $stock->decrement('quantity', $line['quantity']);
                    if ($stock->quantity <= 0) {
                        $stock->update(['is_active' => false]);
                    }
                    StockTransaction::create([
                        'stock_id'     => $stock->id,
                        'medicine_id'  => $medicineLocalId,
                        'type'         => 'sale',
                        'quantity'     => -$line['quantity'],
                        'unit_price'   => $item['rate'],
                        'total_amount' => $line['quantity'] * $item['rate'],
                        'invoice_id'   => $invoice->id,
                        'batch_number' => $stock->batch_number,
                        'expiry_date'  => $stock->expiry_date,
                        'notes'        => 'FEFO sale',
                        'user_id'      => auth()->id(),
                    ]);
                }

                $medicine = Medicine::find($medicineLocalId);
                if ($medicine) {
                    app(ControlledDispenseRecorder::class)->record(
                        $medicine,
                        $result['lines'],
                        $invoice->customer_id,
                        $invoice->branch_id,
                        auth()->id(),
                        'pos',
                        null,
                        $invoice,
                    );
                }
            }

            $payments = $request->input('payments', []);
            if (! is_array($payments) || $payments === []) {
                $method   = $request->payment_type === 'mixed' ? 'cash' : $request->payment_type;
                $payments = [['method' => $method, 'amount' => $request->paid_amount ?? 0]];
            }
            foreach ($payments as $payment) {
                if ((float) ($payment['amount'] ?? 0) <= 0) {
                    continue;
                }
                TenderPayment::create([
                    'payable_type' => Invoice::class,
                    'payable_id'   => $invoice->id,
                    'method'       => $payment['method'],
                    'amount'       => $payment['amount'],
                ]);
            }
        });

        $tenders  = TenderPayment::where('payable_type', Invoice::class)->where('payable_id', $invoice->id)->get();
        $cashLike = ['cash', 'card', 'bkash', 'nagad', 'rocket'];
        $cashPart = (float) $tenders->whereIn('method', $cashLike)->sum('amount');
        $bankPart = (float) $tenders->where('method', 'bank')->sum('amount');
        $due      = (float) $invoice->due_amount;
        app(JournalPoster::class)->post($invoice->branch_id, $invoice->date->toDateString(), 'invoice', $invoice->id, 'Sale '.$invoice->invoice_no, [
            ['code' => '1000', 'debit' => $cashPart],
            ['code' => '1010', 'debit' => $bankPart],
            ['code' => '1100', 'debit' => $due],
            ['code' => '4000', 'credit' => (float) $invoice->total_amount],
            ['code' => '5000', 'debit' => $saleCost],
            ['code' => '1200', 'credit' => $saleCost],
        ]);

        // Conditional notifications
        try {
            $customer = Customer::find($customerId);
            if ($customer && $customer->mobile !== 'WALK-IN') {
                // Load invoice relations for messaging
                $invoice->load(['items.medicine', 'customer']);

                // Load settings
                $settings = [];
                try {
                    $settingsRaw = Storage::get('settings.json');
                    $settings    = json_decode($settingsRaw, true) ?: [];
                } catch (\Throwable $e) {
                }

                $companyName      = $settings['company_name'] ?? config('app.name', 'PharmaCare');
                $currencySymbol   = $settings['currency_symbol'] ?? ($request->user()?->ui['currency_symbol'] ?? '$');
                $currencyPosition = $settings['currency_position'] ?? 'before';

                $formatMoney = function ($amount) use ($currencySymbol, $currencyPosition) {
                    $val = number_format((float) $amount, 2);

                    return $currencyPosition === 'before' ? ($currencySymbol.$val) : ($val.$currencySymbol);
                };

                $itemsCount = $invoice->items->count();
                $smsText    = 'Invoice #'.$invoice->invoice_no
                    .' | Date '.($invoice->date ? date('Y-m-d', strtotime($invoice->date)) : date('Y-m-d'))
                    .' | Items '.$itemsCount
                    .' | Total '.$formatMoney($invoice->total_amount)
                    .' | Paid '.$formatMoney($invoice->paid_amount)
                    .' | Due '.$formatMoney($invoice->due_amount)
                    .' | '.$companyName.' - Thank you!';

                if ($request->boolean('send_sms') && ! empty($customer->mobile)) {
                    NotificationHelper::sendSms($customer->mobile, $smsText);
                }
                if ($request->boolean('send_email') && ! empty($customer->email)) {
                    // Send rich HTML invoice email
                    Mail::to($customer->email)->send(new InvoiceCreatedMail($invoice, $settings));
                }
            }
        } catch (\Throwable $e) {
            // Do not block the flow on notification errors
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.medicine', 'user']);

        return Inertia::render('Invoice/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice)
    {
        $customers = Customer::where('status', true)->get();
        $medicines = Medicine::with(['category', 'manufacturer'])
            ->where('status', true)
            ->get();
        $invoice->load(['items.medicine', 'customer']);
        $payload                = $invoice->toArray();
        $payload['customer_id'] = $invoice->customer?->publicId();
        $payload['items']       = collect($invoice->items)->map(function ($item) {
            $row                = $item->toArray();
            $row['medicine_id'] = $item->medicine?->publicId();

            return $row;
        })->values()->all();

        return Inertia::render('Invoice/Edit', [
            'invoice'   => $payload,
            'customers' => $customers,
            'medicines' => $medicines,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id'         => 'required|exists:customers,customer_id',
            'date'                => 'required|date',
            'payment_type'        => 'required|in:cash,bank,credit',
            'items'               => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,medicine_id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.rate'        => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'customer_id'      => Customer::localIdOrFail($request->customer_id),
            'date'             => $request->date,
            'total_amount'     => $request->total_amount,
            'total_tax'        => $request->total_tax ?? 0,
            'previous_due'     => $request->previous_due ?? 0,
            'paid_amount'      => $request->paid_amount ?? 0,
            'due_amount'       => $request->due_amount ?? 0,
            'total_discount'   => $request->total_discount ?? 0,
            'invoice_discount' => $request->invoice_discount ?? 0,
            'details'          => $request->details,
            'payment_type'     => $request->payment_type,
        ]);

        // Delete existing items and create new ones
        $invoice->items()->delete();
        foreach ($request->items as $item) {
            InvoiceItem::create([
                'invoice_id'   => $invoice->id,
                'medicine_id'  => Medicine::localIdOrFail($item['medicine_id']),
                'batch_id'     => $item['batch_id'] ?? 'BATCH001',
                'quantity'     => $item['quantity'],
                'rate'         => $item['rate'],
                'discount'     => $item['discount'] ?? 0,
                'total_amount' => $item['quantity'] * $item['rate'] - ($item['discount'] ?? 0),
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function pos()
    {
        $today = now()->toDateString();

        return Inertia::render('Invoice/POS', [
            'medicines'  => $this->posMedicineCatalog('', 'all', 36),
            'invoiceNo'  => $this->generateInvoiceNumber(),
            'heldBills'  => HeldBill::where('user_id', auth()->id())->where('status', 'held')->latest()->limit(8)->get(),
            'resume'     => session('held_payload'),
            'todaySales' => (float) Invoice::whereDate('date', $today)->sum('total_amount'),
            'todayCount' => Invoice::whereDate('date', $today)->count(),
        ]);
    }

    public function hold(Request $request)
    {
        $data = $request->validate([
            'label'   => 'nullable|string|max:100',
            'payload' => 'required|array',
        ]);
        HeldBill::create([
            'branch_id'  => BranchContext::id(),
            'counter_id' => $request->user()?->counter_id,
            'user_id'    => $request->user()->id,
            'label'      => $data['label'] ?? 'Held bill',
            'payload'    => $data['payload'],
            'status'     => 'held',
        ]);

        return back()->with('success', 'Bill held.');
    }

    public function resumeHeld(HeldBill $heldBill)
    {
        abort_unless($heldBill->user_id === auth()->id(), 403);
        $heldBill->update(['status' => 'resumed']);

        return redirect()->route('pos')->with('held_payload', $heldBill->payload);
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.medicine', 'user']);

        return Inertia::render('Invoice/Print', [
            'invoice' => $invoice,
        ]);
    }

    public function searchMedicines(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $kind = (string) $request->get('kind', 'all');
        $limit = min(max((int) $request->get('limit', 36), 1), 60);

        return response()->json($this->posMedicineCatalog($q, $kind, $limit));
    }

    /**
     * POS catalog: lean columns, stock sum, name + generic search.
     *
     * @return \Illuminate\Support\Collection<int, Medicine>
     */
    private function posMedicineCatalog(string $q, string $kind = 'all', int $limit = 36)
    {
        $stockScope = function ($query) {
            $query->where('quantity', '>', 0)
                ->where(function ($inner) {
                    $inner->whereNull('status')->orWhere('status', 'available');
                })
                ->where(function ($inner) {
                    $inner->where('recalled', false)->orWhereNull('recalled');
                });
            BranchContext::constrainStock($query);
        };

        $builder = Medicine::query()
            ->select([
                'medicines.id',
                'medicines.medicine_id',
                'medicines.name',
                'medicines.generic_name',
                'medicines.generic_id',
                'medicines.category_id',
                'medicines.manufacturer_id',
                'medicines.strength',
                'medicines.dosage_form',
                'medicines.dosage_form_id',
                'medicines.medicine_type_id',
                'medicines.sku',
                'medicines.product_id',
                'medicines.barcode_data',
                'medicines.price',
                'medicines.image',
                'medicines.requires_prescription',
                'medicines.is_controlled',
                'medicines.is_narcotic',
                'medicines.status',
            ])
            ->with([
                'category:id,category_id,name',
                'manufacturer:id,manufacturer_id,name',
                'generic:id,generic_id,name,segment',
                'medicineType:id,medicine_type_id,name',
                'dosageForm:id,dosage_form_id,name',
                'units:id,medicine_id,name,factor_to_base',
            ])
            ->withSum(['stocks as stock_qty' => $stockScope], 'quantity')
            ->where('medicines.status', true);

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $like = '%'.$escaped.'%';
            $prefix = $escaped.'%';

            $builder->where(function ($w) use ($q, $like) {
                $w->where('medicines.name', 'like', $like)
                    ->orWhere('medicines.generic_name', 'like', $like)
                    ->orWhere('medicines.dosage_form', 'like', $like)
                    ->orWhere('medicines.sku', $q)
                    ->orWhere('medicines.product_id', $q)
                    ->orWhere('medicines.barcode_data', $q)
                    ->orWhereHas('generic', function ($g) use ($like) {
                        $g->where('name', 'like', $like);
                    })
                    ->orWhereHas('dosageForm', function ($f) use ($like) {
                        $f->where('name', 'like', $like);
                    })
                    ->orWhereHas('brand', function ($b) use ($like) {
                        $b->where('name', 'like', $like);
                    })
                    ->orWhereIn('medicines.medex_id', function ($sub) use ($like) {
                        $sub->select('medex_id')
                            ->from('medex_brand_indexes')
                            ->whereNotNull('medex_id')
                            ->where(function ($i) use ($like) {
                                $i->where('name', 'like', $like)
                                    ->orWhere('generic_name', 'like', $like)
                                    ->orWhere('form', 'like', $like);
                            });
                    });
            });

            $builder->orderByRaw(
                'CASE
                    WHEN medicines.barcode_data = ? OR medicines.sku = ? OR medicines.product_id = ? THEN 0
                    WHEN medicines.name LIKE ? THEN 1
                    WHEN medicines.generic_name LIKE ? THEN 2
                    ELSE 3
                END',
                [$q, $q, $q, $prefix, $prefix]
            );
        }

        if ($kind === 'Rx') {
            $builder->where('medicines.requires_prescription', true);
        } elseif ($kind === 'OTC') {
            $builder->where(function ($w) {
                $w->where('medicines.requires_prescription', false)
                    ->orWhereNull('medicines.requires_prescription');
            })->whereDoesntHave('category', function ($c) {
                $c->where(function ($n) {
                    $n->where('name', 'like', '%device%')
                        ->orWhere('name', 'like', '%supplement%');
                });
            });
        } elseif ($kind === 'Supplement') {
            $builder->whereHas('category', fn ($c) => $c->where('name', 'like', '%supplement%'));
        } elseif ($kind === 'Device') {
            $builder->where(function ($w) {
                $w->whereHas('category', fn ($c) => $c->where('name', 'like', '%device%'))
                    ->orWhereHas('medicineType', fn ($t) => $t->where('name', 'Device'));
            });
        } elseif ($kind === 'Herbal') {
            $builder->whereHas('medicineType', fn ($t) => $t->where('name', 'Herbal'));
        }

        if ($q === '') {
            $builder->orderByRaw('COALESCE(stock_qty, 0) DESC')->orderBy('medicines.name');
        } else {
            $builder->orderBy('medicines.name');
        }

        return $builder->limit($limit)->get()->map(function (Medicine $medicine) {
            if ($medicine->image && ! str_starts_with((string) $medicine->image, 'http') && ! str_starts_with((string) $medicine->image, '/')) {
                $medicine->setAttribute('image', Storage::disk('public')->url($medicine->image));
            }

            return $medicine;
        });
    }

    public function searchCustomers(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        if ($query === '') {
            return response()->json([]);
        }

        $customers = Customer::query()
            ->where('status', true)
            ->where('mobile', '!=', 'WALK-IN')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('mobile', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'customer_id', 'name', 'mobile', 'phone', 'email']);

        return response()->json($customers);
    }

    public function quickStoreCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:30|unique:customers,mobile',
        ]);

        $customer = Customer::create([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'status' => true,
        ]);

        return response()->json($customer);
    }

    public function availableStocks(Medicine $medicine)
    {
        $stocks = Stock::where('medicine_id', $medicine->id)
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'available');
            })
            ->where(function ($q) {
                $q->where('recalled', false)->orWhereNull('recalled');
            })
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->get(['id', 'batch_number', 'expiry_date', 'quantity', 'selling_price', 'mrp']);

        return response()->json($stocks);
    }

    private function generateInvoiceId()
    {
        do {
            $invoiceId = Str::random(10);
        } while (Invoice::where('invoice_id', $invoiceId)->exists());

        return $invoiceId;
    }

    private function generateInvoiceNumber()
    {
        try {
            $settings = Storage::get('settings.json');
            if ($settings) {
                $decoded    = json_decode($settings, true);
                $nextNumber = $decoded['next_invoice_number'] ?? 1000;

                // Update the next invoice number for next time
                $decoded['next_invoice_number'] = $nextNumber + 1;
                Storage::put('settings.json', json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return $nextNumber;
            }
        } catch (\Exception $e) {
            // Fallback to default behavior
        }

        $lastInvoice = Invoice::orderBy('id', 'desc')->first();

        return $lastInvoice ? $lastInvoice->invoice_no + 1 : 1000;
    }

    private function getInvoicePrefix()
    {
        try {
            $settings = Storage::get('settings.json');
            if ($settings) {
                $decoded = json_decode($settings, true);

                return $decoded['invoice_prefix'] ?? 'INV';
            }
        } catch (\Exception $e) {
            // Fallback to default
        }

        return 'INV'; // Default prefix
    }
}
