<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

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
        $banks = Bank::where('status', true)->get();
        $invoiceNo = $this->generateInvoiceNumber();

        return Inertia::render('Invoice/Create', [
            'customers' => $customers,
            'medicines' => $medicines,
            'banks' => $banks,
            'invoiceNo' => $invoiceNo,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'payment_type' => 'required|in:cash,bank,credit',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::create([
            'invoice_id'       => $this->generateInvoiceId(),
            'customer_id'      => $request->customer_id,
            'date'             => $request->date,
            'invoice_no'       => $request->invoice_no,
            'total_amount'     => $request->total_amount,
            'total_tax'        => $request->total_tax ?? 0,
            'previous_due'     => $request->previous_due ?? 0,
            'paid_amount'      => $request->paid_amount ?? 0,
            'due_amount'       => $request->due_amount ?? 0,
            'total_discount'   => $request->total_discount ?? 0,
            'invoice_discount' => $request->invoice_discount ?? 0,
            'bank_id'          => $request->bank_id,
            'user_id'          => auth()->id(),
            'details'          => $request->details,
            'payment_type'     => $request->payment_type,
        ]);

        // Create invoice items
        foreach ($request->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'medicine_id' => $item['medicine_id'],
                'batch_id' => $item['batch_id'] ?? 'BATCH001',
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'discount' => $item['discount'] ?? 0,
                'total_amount' => $item['quantity'] * $item['rate'] - ($item['discount'] ?? 0),
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.medicine', 'bank', 'user']);

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
        $banks = Bank::where('status', true)->get();
        $invoice->load(['items.medicine']);

        return Inertia::render('Invoice/Edit', [
            'invoice' => $invoice,
            'customers' => $customers,
            'medicines' => $medicines,
            'banks' => $banks,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'payment_type' => 'required|in:cash,bank,credit',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'customer_id' => $request->customer_id,
            'date' => $request->date,
            'total_amount' => $request->total_amount,
            'total_tax' => $request->total_tax ?? 0,
            'previous_due' => $request->previous_due ?? 0,
            'paid_amount' => $request->paid_amount ?? 0,
            'due_amount' => $request->due_amount ?? 0,
            'total_discount' => $request->total_discount ?? 0,
            'invoice_discount' => $request->invoice_discount ?? 0,
            'bank_id' => $request->bank_id,
            'details' => $request->details,
            'payment_type' => $request->payment_type,
        ]);

        // Delete existing items and create new ones
        $invoice->items()->delete();
        foreach ($request->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'medicine_id' => $item['medicine_id'],
                'batch_id' => $item['batch_id'] ?? 'BATCH001',
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'discount' => $item['discount'] ?? 0,
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
        $banks = Bank::where('status', true)->get();
        $invoiceNo = $this->generateInvoiceNumber();

        return Inertia::render('Invoice/POS', [
            'customers' => $customers,
            'medicines' => $medicines,
            'banks' => $banks,
            'invoiceNo' => $invoiceNo,
        ]);
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.medicine', 'bank', 'user']);

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

    private function generateInvoiceId()
    {
        do {
            $invoiceId = Str::random(10);
        } while (Invoice::where('invoice_id', $invoiceId)->exists());

        return $invoiceId;
    }

    private function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        return $lastInvoice ? $lastInvoice->invoice_no + 1 : 1000;
    }
}
