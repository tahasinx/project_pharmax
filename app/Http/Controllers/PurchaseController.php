<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['manufacturer', 'items.medicine'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Purchase/Index', [
            'purchases' => $purchases,
        ]);
    }

    public function create()
    {
        $manufacturers = Manufacturer::where('status', true)->get();
        $medicines = Medicine::with(['category', 'manufacturer'])
            ->where('status', true)
            ->get();

        return Inertia::render('Purchase/Create', [
            'manufacturers' => $manufacturers,
            'medicines' => $medicines,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::create([
            'purchase_id'     => $this->generatePurchaseId(),
            'manufacturer_id' => $request->manufacturer_id,
            'purchase_date'   => $request->purchase_date,
            'purchase_no'     => $this->generatePurchaseNumber(),
            'grand_total'     => $request->grand_total,
            'total_tax'       => $request->total_tax ?? 0,
            'total_discount'  => $request->total_discount ?? 0,
            'user_id'         => auth()->id(),
            'details'         => $request->details,
            'status'          => $request->status ?? true,
        ]);

        // Create purchase items and update stock
        DB::transaction(function () use ($request, $purchase) {
            foreach ($request->items as $item) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'batch_id' => $item['batch_id'] ?? 'BATCH001',
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'discount' => $item['discount'] ?? 0,
                    'total_amount' => $item['quantity'] * $item['rate'] - ($item['discount'] ?? 0),
                ]);

                // Update or create stock
                $stock = Stock::where('medicine_id', $item['medicine_id'])
                    ->where('batch_number', $item['batch_id'] ?? 'BATCH001')
                    ->where('is_active', true)
                    ->first();

                if ($stock) {
                    // Update existing stock
                    $stock->increment('quantity', $item['quantity']);
                    $stock->update([
                        'purchase_price' => $item['rate'],
                        'supplier' => $purchase->manufacturer->name ?? 'Unknown',
                    ]);
                } else {
                    // Create new stock entry
                    $stock = Stock::create([
                        'medicine_id' => $item['medicine_id'],
                        'purchase_id' => $purchase->id,
                        'batch_number' => $item['batch_id'] ?? 'BATCH001',
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'quantity' => $item['quantity'],
                        'min_stock_level' => 10, // Default minimum
                        'max_stock_level' => 100, // Default maximum
                        'purchase_price' => $item['rate'],
                        'selling_price' => $item['rate'] * 1.2, // 20% markup by default
                        'supplier' => $purchase->manufacturer->name ?? 'Unknown',
                        'notes' => 'Created from purchase',
                        'is_active' => true,
                    ]);
                }

                // Create stock transaction
                StockTransaction::create([
                    'stock_id' => $stock->id,
                    'medicine_id' => $item['medicine_id'],
                    'type' => 'purchase',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['rate'],
                    'total_amount' => $item['quantity'] * $item['rate'],
                    'purchase_id' => $purchase->id,
                    'batch_number' => $item['batch_id'] ?? 'BATCH001',
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'notes' => 'Stock added from purchase',
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['manufacturer', 'items.medicine', 'user']);

        return Inertia::render('Purchase/Show', [
            'purchase' => $purchase,
        ]);
    }

    public function edit(Purchase $purchase)
    {
        $manufacturers = Manufacturer::where('status', true)->get();
        $medicines = Medicine::with(['category', 'manufacturer'])
            ->where('status', true)
            ->get();
        $purchase->load(['items.medicine']);

        return Inertia::render('Purchase/Edit', [
            'purchase' => $purchase,
            'manufacturers' => $manufacturers,
            'medicines' => $medicines,
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $purchase->update([
            'manufacturer_id' => $request->manufacturer_id,
            'purchase_date'   => $request->purchase_date,
            'grand_total'     => $request->grand_total,
            'total_tax'       => $request->total_tax ?? 0,
            'total_discount'  => $request->total_discount ?? 0,
            'details'         => $request->details,
            'status'          => $request->status ?? $purchase->status,
        ]);

        // Delete existing items and create new ones
        $purchase->items()->delete();
        foreach ($request->items as $item) {
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'medicine_id' => $item['medicine_id'],
                'batch_id' => $item['batch_id'] ?? 'BATCH001',
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'discount' => $item['discount'] ?? 0,
                'total_amount' => $item['quantity'] * $item['rate'] - ($item['discount'] ?? 0),
            ]);
        }

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }

    private function generatePurchaseId()
    {
        do {
            $purchaseId = Str::random(10);
        } while (Purchase::where('purchase_id', $purchaseId)->exists());

        return $purchaseId;
    }

    private function generatePurchaseNumber()
    {
        $lastPurchase = Purchase::orderBy('id', 'desc')->first();
        return $lastPurchase ? $lastPurchase->purchase_no + 1 : 1000;
    }
}
