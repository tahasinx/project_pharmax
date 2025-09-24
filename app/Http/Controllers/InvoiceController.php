<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Traits\HasSettingsPagination;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    use HasSettingsPagination;

    public function index()
    {
        $itemsPerPage = $this->getItemsPerPage();
        $invoices = Invoice::with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate($itemsPerPage);

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
        $invoiceNo = $this->generateInvoiceNumber();
        $invoicePrefix = $this->getInvoicePrefix();

        return Inertia::render('Invoice/Create', [
            'customers' => $customers,
            'medicines' => $medicines,
            'invoiceNo' => $invoiceNo,
            'invoicePrefix' => $invoicePrefix,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'date'                  => 'required|date',
            'payment_type'          => 'required|in:cash,bank,credit',
            'paid_amount'           => 'nullable|numeric|min:0',
            'due_amount'            => 'nullable|numeric|min:0',
            'total_amount'          => 'required|numeric|min:0',
            'total_tax'             => 'nullable|numeric|min:0',
            'total_discount'        => 'nullable|numeric|min:0',
            'items'                 => 'required|array|min:1',
            'items.*.medicine_id'   => 'required|exists:medicines,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.rate'          => 'required|numeric|min:0',
        ]);

        // Recalculate due on server for accuracy and safety
        $totalAmount = (float) ($request->total_amount ?? 0);
        $paidAmount  = (float) ($request->paid_amount ?? 0);
        $dueAmount   = max(round($totalAmount - $paidAmount, 2), 0);

        $invoice = Invoice::create([
            'invoice_id'         => $this->generateInvoiceId(),
            'customer_id'        => $request->customer_id,
            'date'               => $request->date,
            'invoice_no'         => $request->invoice_no,
            'total_amount'       => $totalAmount,
            'total_tax'          => $request->total_tax ?? 0,
            'previous_due'       => $request->previous_due ?? 0,
            'paid_amount'        => $paidAmount,
            'due_amount'         => $dueAmount,
            'total_discount'     => $request->total_discount ?? 0,
            'invoice_discount'   => $request->invoice_discount ?? 0,
            'user_id'            => auth()->id(),
            'details'            => $request->details,
            'payment_type'       => $request->payment_type,
        ]);

        // Create invoice items and update stock
        DB::transaction(function () use ($request, $invoice) {
            foreach ($request->items as $item) {
                $invoiceItem = InvoiceItem::create([
                    'invoice_id'   => $invoice->id,
                    'medicine_id'  => $item['medicine_id'],
                    'batch_id'     => $item['batch_id'] ?? 'BATCH001',
                    'quantity'     => $item['quantity'],
                    'rate'         => $item['rate'],
                    'discount'     => $item['discount'] ?? 0,
                    'total_amount' => $item['quantity'] * $item['rate'] - ($item['discount'] ?? 0),
                ]);

                // Find and update stock (FIFO - First In, First Out)
                $stocks = Stock::where('medicine_id', $item['medicine_id'])
                    ->where('is_active', true)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc') // FIFO by expiry date
                    ->get();

                $remainingQuantity = $item['quantity'];
                $totalCost = 0;

                foreach ($stocks as $stock) {
                    if ($remainingQuantity <= 0) break;

                    $deductQuantity = min($remainingQuantity, $stock->quantity);

                    // Update stock quantity
                    $stock->decrement('quantity', $deductQuantity);
                    $remainingQuantity -= $deductQuantity;
                    $totalCost += $deductQuantity * $stock->purchase_price;

                    // Create stock transaction for sale
                    StockTransaction::create([
                        'stock_id' => $stock->id,
                        'medicine_id' => $item['medicine_id'],
                        'type' => 'sale',
                        'quantity' => -$deductQuantity, // Negative for sale
                        'unit_price' => $item['rate'],
                        'total_amount' => $deductQuantity * $item['rate'],
                        'invoice_id' => $invoice->id,
                        'batch_number' => $stock->batch_number,
                        'expiry_date' => $stock->expiry_date,
                        'notes' => 'Stock sold via invoice',
                        'user_id' => auth()->id(),
                    ]);

                    // If stock is depleted, mark as inactive
                    if ($stock->quantity <= 0) {
                        $stock->update(['is_active' => false]);
                    }
                }

                // If not enough stock available, return a validation error
                if ($remainingQuantity > 0) {
                    $medicine = Medicine::find($item['medicine_id']);
                    $available = $item['quantity'] - $remainingQuantity;
                    $message = 'Insufficient stock';
                    if ($medicine) {
                        $message = "Insufficient stock for {$medicine->name}. Required: {$item['quantity']}, Available: {$available}";
                    }
                    throw ValidationException::withMessages([
                        'items' => [$message],
                    ]);
                }
            }
        });

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
        $invoice->load(['items.medicine']);

        return Inertia::render('Invoice/Edit', [
            'invoice'   => $invoice,
            'customers' => $customers,
            'medicines' => $medicines,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'date'                => 'required|date',
            'payment_type'        => 'required|in:cash,bank,credit',
            'items'               => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.rate'        => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'customer_id'      => $request->customer_id,
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
                'medicine_id'  => $item['medicine_id'],
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
        $customers = Customer::where('status', true)->get();
        $medicines = Medicine::with(['category', 'manufacturer'])
            ->where('status', true)
            ->get();
        $invoiceNo = $this->generateInvoiceNumber();

        return Inertia::render('Invoice/POS', [
            'customers' => $customers,
            'medicines' => $medicines,
            'invoiceNo' => $invoiceNo,
        ]);
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
        $query = $request->get('q');

        $medicines = Medicine::with(['category', 'manufacturer'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('generic_name', 'like', "%{$query}%")
            ->where('status', true)
            ->limit(10)
            ->get();

        return response()->json($medicines);
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q');

        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('mobile', 'like', "%{$query}%")
            ->where('status', true)
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    public function availableStocks(Medicine $medicine)
    {
        $stocks = Stock::where('medicine_id', $medicine->id)
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get(['id', 'batch_number', 'expiry_date', 'quantity', 'selling_price']);

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
            $settings = \Illuminate\Support\Facades\Storage::get('settings.json');
            if ($settings) {
                $decoded = json_decode($settings, true);
                $nextNumber = $decoded['next_invoice_number'] ?? 1000;

                // Update the next invoice number for next time
                $decoded['next_invoice_number'] = $nextNumber + 1;
                \Illuminate\Support\Facades\Storage::put('settings.json', json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

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
            $settings = \Illuminate\Support\Facades\Storage::get('settings.json');
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
