<?php

namespace App\Http\Controllers;

use App\Domain\Finance\JournalPoster;
use App\Domain\Organization\BranchContext;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Traits\HasSettingsPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    use HasSettingsPagination;

    public function index()
    {
        $itemsPerPage = $this->getItemsPerPage();
        $purchases    = BranchContext::constrain(
            Purchase::with(['manufacturer', 'items.medicine'])->orderBy('created_at', 'desc')
        )->paginate($itemsPerPage)->withQueryString();

        return Inertia::render('Purchase/Index', [
            'purchases' => $purchases,
        ]);
    }

    public function create()
    {
        $manufacturers = Manufacturer::where('status', true)->get();
        $medicines     = Medicine::with(['category', 'manufacturer'])
            ->where('status', true)
            ->get();

        return Inertia::render('Purchase/Create', [
            'manufacturers' => $manufacturers,
            'medicines'     => $medicines,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'manufacturer_id'     => 'required|exists:manufacturers,manufacturer_id',
            'purchase_date'       => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,medicine_id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.rate'        => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::create([
            'purchase_id'     => $this->generatePurchaseId(),
            'branch_id'       => BranchContext::id(),
            'manufacturer_id' => Manufacturer::localIdOrFail($request->manufacturer_id),
            'purchase_date'   => $request->purchase_date,
            'purchase_no'     => $this->generatePurchaseNumber(),
            'chalan_no'       => $request->chalan_no ?? $this->generateChalanNumber(),
            'payment_type'    => $request->payment_type ?? 'cash',
            'grand_total'     => $request->grand_total,
            'total_tax'       => $request->total_tax ?? 0,
            'total_discount'  => $request->total_discount ?? 0,
            'paid_amount'     => $request->paid_amount ?? 0,
            'due_amount'      => $request->due_amount ?? $request->grand_total,
            'total_vat'       => $request->total_vat ?? 0,
            'bank_id'         => $request->bank_id ?? null,
            'user_id'         => auth()->id(),
            'details'         => $request->details,
            'status'          => $request->status ?? true,
        ]);

        // Create purchase items and update stock
        DB::transaction(function () use ($request, $purchase) {
            foreach ($request->items as $item) {
                $medicineLocalId = Medicine::localIdOrFail($item['medicine_id']);
                $purchaseItem    = PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'medicine_id'  => $medicineLocalId,
                    'batch_id'     => $item['batch_id'] ?? 'BATCH001',
                    'quantity'     => $item['quantity'],
                    'rate'         => $item['rate'],
                    'discount'     => $item['discount'] ?? 0,
                    'total_amount' => $item['quantity'] * $item['rate'] - ($item['discount'] ?? 0),
                ]);

                // Update or create stock
                $stock = Stock::where('medicine_id', $medicineLocalId)
                    ->where('batch_number', $item['batch_id'] ?? 'BATCH001')
                    ->where('is_active', true)
                    ->first();

                if ($stock) {
                    // Update existing stock
                    $stock->increment('quantity', $item['quantity']);
                    $stock->update([
                        'purchase_price' => $item['rate'],
                        'supplier'       => $purchase->manufacturer->name ?? 'Unknown',
                        'branch_id'      => $stock->branch_id ?? BranchContext::id(),
                        'status'         => $stock->status ?: 'available',
                    ]);
                } else {
                    // Create new stock entry
                    $stock = Stock::create([
                        'medicine_id'     => $medicineLocalId,
                        'purchase_id'     => $purchase->id,
                        'batch_number'    => $item['batch_id'] ?? 'BATCH001',
                        'expiry_date'     => $item['expiry_date'] ?? null,
                        'quantity'        => $item['quantity'],
                        'min_stock_level' => 10, // Default minimum
                        'max_stock_level' => 100, // Default maximum
                        'purchase_price'  => $item['rate'],
                        'selling_price'   => $item['rate'] * 1.2, // 20% markup by default
                        'supplier'        => $purchase->manufacturer->name ?? 'Unknown',
                        'notes'           => 'Created from purchase',
                        'is_active'       => true,
                        'branch_id'       => BranchContext::id(),
                        'status'          => 'available',
                    ]);
                }

                // Create stock transaction
                StockTransaction::create([
                    'stock_id'     => $stock->id,
                    'medicine_id'  => $medicineLocalId,
                    'type'         => 'purchase',
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['rate'],
                    'total_amount' => $item['quantity'] * $item['rate'],
                    'purchase_id'  => $purchase->id,
                    'batch_number' => $item['batch_id'] ?? 'BATCH001',
                    'expiry_date'  => $item['expiry_date'] ?? null,
                    'notes'        => 'Stock added from purchase',
                    'user_id'      => auth()->id(),
                ]);
            }
        });

        $total = (float) $purchase->grand_total;
        $paid  = (float) $purchase->paid_amount;
        $due   = max($total - $paid, 0);
        $cash  = $purchase->payment_type === 'bank' ? 0 : $paid;
        $bank  = $purchase->payment_type === 'bank' ? $paid : 0;
        app(JournalPoster::class)->post($purchase->branch_id, $purchase->purchase_date->toDateString(), 'purchase', $purchase->id, 'Purchase '.$purchase->purchase_id, [
            ['code' => '1200', 'debit' => $total],
            ['code' => '1000', 'credit' => $cash],
            ['code' => '1010', 'credit' => $bank],
            ['code' => '2000', 'credit' => $due],
        ]);

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
        $medicines     = Medicine::with(['category', 'manufacturer'])
            ->where('status', true)
            ->get();
        $purchase->load(['manufacturer', 'items.medicine']);
        $payload                    = $purchase->toArray();
        $payload['manufacturer_id'] = $purchase->manufacturer?->publicId();
        $payload['items']           = collect($purchase->items)->map(function ($item) {
            $row                = $item->toArray();
            $row['medicine_id'] = $item->medicine?->publicId();

            return $row;
        })->values()->all();

        return Inertia::render('Purchase/Edit', [
            'purchase'      => $payload,
            'manufacturers' => $manufacturers,
            'medicines'     => $medicines,
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'manufacturer_id'     => 'required|exists:manufacturers,manufacturer_id',
            'purchase_date'       => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,medicine_id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.rate'        => 'required|numeric|min:0',
        ]);

        $purchase->update([
            'manufacturer_id' => Manufacturer::localIdOrFail($request->manufacturer_id),
            'purchase_date'   => $request->purchase_date,
            'chalan_no'       => $request->chalan_no ?? $purchase->chalan_no,
            'payment_type'    => $request->payment_type ?? $purchase->payment_type,
            'grand_total'     => $request->grand_total,
            'total_tax'       => $request->total_tax ?? 0,
            'total_discount'  => $request->total_discount ?? 0,
            'paid_amount'     => $request->paid_amount ?? $purchase->paid_amount,
            'due_amount'      => $request->due_amount ?? $request->grand_total,
            'total_vat'       => $request->total_vat ?? 0,
            'bank_id'         => $request->bank_id ?? $purchase->bank_id,
            'details'         => $request->details,
            'status'          => $request->status ?? $purchase->status,
        ]);

        // Delete existing items and create new ones
        $purchase->items()->delete();
        foreach ($request->items as $item) {
            PurchaseItem::create([
                'purchase_id'  => $purchase->id,
                'medicine_id'  => Medicine::localIdOrFail($item['medicine_id']),
                'batch_id'     => $item['batch_id'] ?? 'BATCH001',
                'quantity'     => $item['quantity'],
                'rate'         => $item['rate'],
                'discount'     => $item['discount'] ?? 0,
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

    private function generateChalanNumber()
    {
        $lastPurchase = Purchase::orderBy('id', 'desc')->first();
        $lastChalanNo = $lastPurchase ? $lastPurchase->chalan_no : 'CHL000';
        $number       = (int) str_replace('CHL', '', $lastChalanNo);

        return 'CHL'.str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }
}
