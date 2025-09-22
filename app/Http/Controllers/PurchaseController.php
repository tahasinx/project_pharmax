<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
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

        // Create purchase items
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
