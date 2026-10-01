<?php

namespace App\Http\Controllers;

use App\Domain\Audit\AuditRecorder;
use App\Domain\Finance\JournalPoster;
use App\Domain\Inventory\FefoAllocator;
use App\Domain\Organization\BranchContext;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\ClinicalRule;
use App\Models\ControlledDrugRegister;
use App\Models\Counter;
use App\Models\Customer;
use App\Models\Generic;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalLine;
use App\Models\LedgerAccount;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUnit;
use App\Models\Unit;
use App\Models\Organization;
use App\Models\Prescription;
use App\Models\PurchaseInvoice;
use App\Models\PrescriptionItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PharmacyController extends Controller
{
    public function branches()
    {
        $this->authorizePermission('manage-branches');

        return Inertia::render('Organization/Index', [
            'organization' => Organization::with('branches.warehouses', 'branches.counters')->first(),
            'branches' => Branch::with('warehouses', 'counters')->orderBy('name')->get(),
        ]);
    }

    public function storeBranch(Request $request)
    {
        $this->authorizePermission('manage-branches');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32',
            'is_head_office' => 'nullable|boolean',
        ]);
        $org = Organization::firstOrCreate(['name' => 'Pharmax']);
        $branch = Branch::create([
            'organization_id' => $org->id,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'is_head_office' => $request->boolean('is_head_office'),
        ]);
        Warehouse::create(['branch_id' => $branch->id, 'name' => 'Main Warehouse']);
        Counter::create(['branch_id' => $branch->id, 'name' => 'Main Counter']);
        AuditRecorder::record('organization', $branch, 'created', null, $branch->only(['name', 'code']));

        return back()->with('success', 'Branch created.');
    }

    public function switchBranch(Request $request)
    {
        $data = $request->validate(['branch_id' => 'nullable|exists:branches,id']);
        $user = $request->user();
        if (!empty($data['branch_id']) && !BranchContext::canUse((int) $data['branch_id'], $user)) {
            abort(403);
        }
        if (empty($data['branch_id'])) {
            session()->forget('branch_id');
        } else {
            session(['branch_id' => (int) $data['branch_id']]);
        }

        return back();
    }

    public function generics()
    {
        return $this->nameCatalog(Generic::class, 'Generic Name', 'generics');
    }

    public function storeGeneric(Request $request)
    {
        return $this->storeName(Generic::class, $request, 'generics', 'Generic');
    }

    public function updateGeneric(Request $request, Generic $generic)
    {
        return $this->updateName($generic, $request, 'generics', 'Generic');
    }

    public function destroyGeneric(Generic $generic)
    {
        return $this->destroyName($generic, 'generic');
    }

    public function medicineTypes()
    {
        return $this->nameCatalog(MedicineType::class, 'Medicine Type', 'medicine-types');
    }

    public function storeMedicineType(Request $request)
    {
        return $this->storeName(MedicineType::class, $request, 'medicine_types', 'Medicine type');
    }

    public function updateMedicineType(Request $request, MedicineType $medicineType)
    {
        return $this->updateName($medicineType, $request, 'medicine_types', 'Medicine type');
    }

    public function destroyMedicineType(MedicineType $medicineType)
    {
        return $this->destroyName($medicineType, 'medicine type');
    }

    public function units()
    {
        $this->authorizePermission('manage-medicines');
        $rows = Unit::orderBy('name')->get()->map(function (Unit $unit) {
            $unit->medicines_count = Medicine::where('unit', $unit->name)->count()
                + MedicineUnit::where('name', $unit->name)->distinct()->count('medicine_id');

            return $unit;
        });

        return Inertia::render('Catalog/Index', [
            'title' => 'Units',
            'rows' => $rows,
            'storeRoute' => 'units.store',
            'updateRoute' => 'units.update',
            'destroyRoute' => 'units.destroy',
        ]);
    }

    public function storeUnit(Request $request)
    {
        return $this->storeName(Unit::class, $request, 'units', 'Unit');
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $this->authorizePermission('manage-medicines');
        $data = $request->validate(['name' => 'required|string|max:255|unique:units,name,'.$unit->id]);
        $previous = $unit->name;
        $unit->update($data);
        if ($previous !== $unit->name) {
            Medicine::where('unit', $previous)->update(['unit' => $unit->name]);
            MedicineUnit::where('name', $previous)->update(['name' => $unit->name]);
        }

        return back()->with('success', 'Unit updated.');
    }

    public function destroyUnit(Unit $unit)
    {
        $this->authorizePermission('manage-medicines');
        $used = Medicine::where('unit', $unit->name)->exists()
            || MedicineUnit::where('name', $unit->name)->exists();
        if ($used) {
            return back()->with('error', 'Cannot delete this unit while medicines use it.');
        }
        $unit->delete();

        return back()->with('success', 'Unit deleted.');
    }

    public function brands()
    {
        return $this->nameCatalog(Brand::class, 'Brands', 'brands');
    }

    public function updateBrand(Request $request, Brand $brand)
    {
        return $this->updateName($brand, $request, 'brands', 'Brand');
    }

    public function destroyBrand(Brand $brand)
    {
        return $this->destroyName($brand, 'brand');
    }

    public function storeBrand(Request $request)
    {
        $this->authorizePermission('manage-medicines');
        $data = $request->validate(['name' => 'required|string|max:255|unique:brands,name']);
        Brand::create($data);

        return back()->with('success', 'Brand saved.');
    }

    public function suppliers()
    {
        $this->authorizePermission('manage-suppliers');

        return Inertia::render('Supplier/Index', [
            'suppliers' => Supplier::withCount(['purchaseOrders', 'purchaseReturns'])->orderBy('name')->get(),
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $this->authorizePermission('manage-suppliers');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
        ]);
        Supplier::create($data);

        return back()->with('success', 'Supplier saved.');
    }

    public function purchaseOrders()
    {
        $this->authorizePermission('manage-purchases');

        return Inertia::render('PurchaseOrder/Index', [
            'orders' => PurchaseOrder::with('supplier', 'items.medicine')->latest()->limit(50)->get(),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'medicines' => Medicine::where('status', true)->orderBy('name')->get(['id', 'name']),
            'receipts' => GoodsReceipt::with('supplier', 'items.medicine')->whereNull('invoiced_at')->latest()->limit(30)->get(),
            'batches' => Stock::with('medicine:id,name')->where('quantity', '>', 0)->orderByDesc('id')->limit(200)->get(['id', 'medicine_id', 'batch_number', 'quantity', 'purchase_price', 'status']),
        ]);
    }

    public function storePurchaseOrder(Request $request)
    {
        $this->authorizePermission('manage-purchases');
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
        ]);
        $branchId = BranchContext::id() ?? Branch::query()->value('id');
        $order = PurchaseOrder::create([
            'branch_id' => $branchId,
            'supplier_id' => $data['supplier_id'],
            'number' => 'PO-' . now()->format('YmdHis'),
            'order_date' => $data['order_date'],
            'notes' => $data['notes'] ?? null,
            'user_id' => $request->user()->id,
            'status' => 'ordered',
        ]);
        foreach ($data['items'] as $item) {
            $order->items()->create($item);
        }

        return back()->with('success', 'Purchase order ' . $order->number . ' created.');
    }

    public function storeGoodsReceipt(Request $request)
    {
        $this->authorizePermission('manage-purchases');
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'received_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.free_quantity' => 'nullable|integer|min:0',
            'items.*.batch_number' => 'required|string|max:100',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.mrp' => 'nullable|numeric|min:0',
        ]);

        $order = PurchaseOrder::with('items')->findOrFail($data['purchase_order_id']);
        $warehouseId = Warehouse::where('branch_id', $order->branch_id)->value('id');

        DB::transaction(function () use ($data, $order, $warehouseId, $request) {
            $receipt = GoodsReceipt::create([
                'purchase_order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'supplier_id' => $order->supplier_id,
                'warehouse_id' => $warehouseId,
                'received_date' => $data['received_date'],
                'user_id' => $request->user()->id,
            ]);

            foreach ($data['items'] as $row) {
                $poItem = PurchaseOrderItem::where('purchase_order_id', $order->id)->findOrFail($row['purchase_order_item_id']);
                $open = $poItem->quantity - $poItem->received_quantity;
                if ($row['quantity'] > $open) {
                    throw ValidationException::withMessages([
                        'items' => ['Received quantity exceeds the open purchase order quantity.'],
                    ]);
                }
                $stock = Stock::create([
                    'medicine_id' => $poItem->medicine_id,
                    'branch_id' => $order->branch_id,
                    'warehouse_id' => $warehouseId,
                    'supplier_id' => $order->supplier_id,
                    'batch_number' => $row['batch_number'],
                    'manufacturing_date' => $row['manufacturing_date'] ?? null,
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'quantity' => $row['quantity'] + (int) ($row['free_quantity'] ?? 0),
                    'free_quantity' => (int) ($row['free_quantity'] ?? 0),
                    'purchase_price' => $row['purchase_price'],
                    'mrp' => $row['mrp'] ?? null,
                    'selling_price' => $row['mrp'] ?? $row['purchase_price'],
                    'min_stock_level' => 10,
                    'status' => 'available',
                    'is_active' => true,
                    'supplier' => Supplier::find($order->supplier_id)?->name,
                ]);
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $poItem->id,
                    'medicine_id' => $poItem->medicine_id,
                    'stock_id' => $stock->id,
                    'batch_number' => $row['batch_number'],
                    'manufacturing_date' => $row['manufacturing_date'] ?? null,
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'quantity' => $row['quantity'],
                    'free_quantity' => (int) ($row['free_quantity'] ?? 0),
                    'purchase_price' => $row['purchase_price'],
                    'mrp' => $row['mrp'] ?? null,
                ]);
                $poItem->increment('received_quantity', $row['quantity']);
                StockTransaction::create([
                    'stock_id' => $stock->id,
                    'medicine_id' => $poItem->medicine_id,
                    'type' => 'purchase',
                    'quantity' => $stock->quantity,
                    'unit_price' => $row['purchase_price'],
                    'total_amount' => $row['quantity'] * $row['purchase_price'],
                    'batch_number' => $row['batch_number'],
                    'expiry_date' => $row['expiry_date'] ?? null,
                    'notes' => 'Goods receipt',
                    'user_id' => $request->user()->id,
                ]);
            }

            $complete = $order->items()->whereColumn('received_quantity', '<', 'quantity')->doesntExist();
            $order->update(['status' => $complete ? 'received' : 'partial']);
        });

        return back()->with('success', 'Goods received. Post a purchase invoice to put the cost on the supplier ledger.');
    }

    public function storePurchaseReturn(Request $request, JournalPoster $poster)
    {
        $this->authorizePermission('manage-purchases');
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:near_expiry,damaged,wrong,short,recall,quality',
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        $stock = Stock::findOrFail($data['stock_id']);
        if ($data['quantity'] > $stock->quantity) {
            throw ValidationException::withMessages(['quantity' => ['Return quantity exceeds batch quantity.']]);
        }
        $credit = $data['quantity'] * (float) $stock->purchase_price;
        $branchId = $stock->branch_id ?? BranchContext::id();

        $return = DB::transaction(function () use ($data, $stock, $credit, $branchId, $request) {
            $return = PurchaseReturn::create([
                'branch_id' => $branchId,
                'supplier_id' => $data['supplier_id'],
                'return_date' => $data['return_date'],
                'total' => $credit,
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);
            PurchaseReturnItem::create([
                'purchase_return_id' => $return->id,
                'stock_id' => $stock->id,
                'quantity' => $data['quantity'],
                'condition' => $data['condition'],
                'credit_amount' => $credit,
            ]);
            $stock->decrement('quantity', $data['quantity']);
            if ($stock->quantity <= 0) {
                $stock->update(['is_active' => false, 'status' => 'returned']);
            }
            AuditRecorder::record('purchase', $return, 'return', null, ['quantity' => $data['quantity'], 'credit' => $credit]);

            return $return;
        });

        $poster->post($branchId, $data['return_date'], 'purchase_return', $return->id, 'Supplier credit', [
            ['code' => '2000', 'debit' => $credit],
            ['code' => '1200', 'credit' => $credit],
        ]);

        return back()->with('success', 'Purchase return posted as supplier credit.');
    }

    public function expiry()
    {
        $this->authorizePermission('manage-inventory');
        $base = Stock::with('medicine')->where('quantity', '>', 0);
        if (!BranchContext::seesAll() && BranchContext::id()) {
            $base->where(function ($q) {
                $q->where('branch_id', BranchContext::id())->orWhereNull('branch_id');
            });
        }
        $stocks = $base->get();
        $buckets = [
            'expired' => [],
            'd0_30' => [],
            'd31_60' => [],
            'd61_90' => [],
            'd91_180' => [],
            'd180' => [],
        ];
        foreach ($stocks as $stock) {
            if (!$stock->expiry_date) {
                continue;
            }
            $days = now()->startOfDay()->diffInDays($stock->expiry_date, false);
            $row = [
                'id' => $stock->id,
                'medicine' => $stock->medicine?->name,
                'batch' => $stock->batch_number,
                'expiry' => $stock->expiry_date->toDateString(),
                'quantity' => $stock->quantity,
                'status' => $stock->status,
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

        return Inertia::render('Stock/Expiry', ['buckets' => $buckets]);
    }

    public function lockBatch(Stock $stock)
    {
        $this->authorizePermission('manage-inventory');
        $old = $stock->only(['status', 'recalled']);
        $stock->update(['status' => 'blocked', 'is_active' => false]);
        AuditRecorder::record('inventory', $stock, 'lock', $old, $stock->only(['status', 'recalled']));

        return back()->with('success', 'Batch locked. It cannot be sold.');
    }

    public function recallBatch(Stock $stock)
    {
        $this->authorizePermission('manage-inventory');
        $old = $stock->only(['status', 'recalled']);
        $stock->update(['recalled' => true, 'status' => 'blocked', 'is_active' => false]);
        AuditRecorder::record('inventory', $stock, 'recall', $old, $stock->only(['status', 'recalled']));

        return back()->with('success', 'Batch blocked for recall.');
    }

    public function transfers()
    {
        $this->authorizePermission('manage-inventory');

        return Inertia::render('Stock/Transfer', [
            'transfers' => StockTransfer::with('stock.medicine', 'fromBranch', 'toBranch')->latest()->limit(40)->get(),
            'branches' => Branch::orderBy('name')->get(),
            'stocks' => Stock::with('medicine')->where('quantity', '>', 0)->where('status', 'available')->orderBy('id', 'desc')->limit(200)->get(),
        ]);
    }

    public function storeTransfer(Request $request)
    {
        $this->authorizePermission('manage-inventory');
        $data = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'to_branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $stock = Stock::findOrFail($data['stock_id']);
        if ($data['quantity'] > $stock->quantity) {
            throw ValidationException::withMessages(['quantity' => ['Quantity exceeds the batch.']]);
        }
        $from = $stock->branch_id ?? Branch::query()->value('id');
        if ((int) $data['to_branch_id'] === (int) $from) {
            throw ValidationException::withMessages(['to_branch_id' => ['Choose a different branch.']]);
        }

        DB::transaction(function () use ($data, $stock, $from, $request) {
            StockTransfer::create([
                'from_branch_id' => $from,
                'to_branch_id' => $data['to_branch_id'],
                'stock_id' => $stock->id,
                'quantity' => $data['quantity'],
                'status' => 'requested',
                'user_id' => $request->user()->id,
            ]);
        });

        return back()->with('success', 'Transfer requested. Dispatch it before the destination can receive the batch.');
    }

    public function dispatchTransfer(StockTransfer $stockTransfer)
    {
        $this->authorizePermission('manage-inventory');
        if ($stockTransfer->status !== 'requested') {
            return back()->with('error', 'Only a requested transfer can be dispatched.');
        }
        $stock = $stockTransfer->stock;
        if ($stockTransfer->quantity > $stock->quantity) {
            return back()->with('error', 'The batch no longer has enough quantity to dispatch.');
        }
        $stock->decrement('quantity', $stockTransfer->quantity);
        if ($stock->quantity <= 0) {
            $stock->update(['is_active' => false]);
        }
        $stockTransfer->update(['status' => 'dispatched']);

        return back()->with('success', 'Transfer dispatched. The batch is in transit.');
    }

    public function receiveTransfer(StockTransfer $stockTransfer)
    {
        $this->authorizePermission('manage-inventory');
        if ($stockTransfer->status !== 'dispatched') {
            return back()->with('error', 'Receive the transfer after it has been dispatched.');
        }
        $stock = $stockTransfer->stock;
        $warehouseId = Warehouse::where('branch_id', $stockTransfer->to_branch_id)->value('id');
        $dest = Stock::create($stock->only([
            'medicine_id', 'batch_number', 'expiry_date', 'manufacturing_date', 'purchase_price',
            'selling_price', 'mrp', 'supplier', 'supplier_id', 'min_stock_level',
        ]) + [
            'branch_id' => $stockTransfer->to_branch_id,
            'warehouse_id' => $warehouseId,
            'quantity' => $stockTransfer->quantity,
            'status' => 'available',
            'is_active' => true,
            'notes' => 'Received from branch transfer',
        ]);
        $stockTransfer->update([
            'destination_stock_id' => $dest->id,
            'status' => 'received',
        ]);

        return back()->with('success', 'Destination branch received the same batch number.');
    }

    public function storePurchaseInvoice(Request $request, JournalPoster $poster)
    {
        $this->authorizePermission('manage-purchases');
        $data = $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id',
            'invoice_date' => 'required|date',
        ]);
        $receipt = GoodsReceipt::with('items')->whereKey($data['goods_receipt_id'])->firstOrFail();
        if ($receipt->invoiced_at) {
            return back()->with('error', 'This receipt already has a purchase invoice.');
        }
        $total = (float)  $receipt->items->sum(fn ($item) => $item->quantity * (float) $item->purchase_price);
        $invoice = PurchaseInvoice::create([
            'goods_receipt_id' => $receipt->id,
            'supplier_id' => $receipt->supplier_id,
            'branch_id' => $receipt->branch_id,
            'number' => 'PI-' . now()->format('YmdHis'),
            'invoice_date' => $data['invoice_date'],
            'total' => $total,
            'user_id' => $request->user()->id,
        ]);
        $receipt->update(['invoiced_at' => now()]);
        $poster->post($receipt->branch_id, $data['invoice_date'], 'purchase_invoice', $invoice->id, 'Purchase invoice ' . $invoice->number, [
            ['code' => '1200', 'debit' => $total],
            ['code' => '2000', 'credit' => $total],
        ]);

        return back()->with('success', 'Purchase invoice posted to the supplier ledger.');
    }

    public function customerReceipt(Request $request, JournalPoster $poster)
    {
        $this->authorizePermission('manage-finance');
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank,card,bkash,nagad,rocket',
        ]);
        $invoice = Invoice::findOrFail($data['invoice_id']);
        if ($data['amount'] > (float) $invoice->due_amount) {
            return back()->with('error', 'The receipt is larger than the amount due.');
        }
        $invoice->update([
            'paid_amount' => (float) $invoice->paid_amount + $data['amount'],
            'due_amount' => (float) $invoice->due_amount - $data['amount'],
        ]);
        $code = $data['method'] === 'bank' ? '1010' : '1000';
        $poster->post($invoice->branch_id, now()->toDateString(), 'customer_receipt', $invoice->id, 'Customer receipt', [
            ['code' => $code, 'debit' => $data['amount']],
            ['code' => '1100', 'credit' => $data['amount']],
        ]);

        return back()->with('success', 'Customer receipt posted.');
    }

    public function supplierPayment(Request $request, JournalPoster $poster)
    {
        $this->authorizePermission('manage-finance');
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank',
        ]);
        $code = $data['method'] === 'bank' ? '1010' : '1000';
        $poster->post(BranchContext::id(), now()->toDateString(), 'supplier_payment', $data['supplier_id'], 'Supplier payment', [
            ['code' => '2000', 'debit' => $data['amount']],
            ['code' => $code, 'credit' => $data['amount']],
        ]);

        return back()->with('success', 'Supplier payment posted.');
    }

    public function clinicalRules()
    {
        $this->authorizePermission('manage-medicines');

        return Inertia::render('Prescription/Rules', [
            'rules' => ClinicalRule::latest()->limit(50)->get(),
            'medicines' => Medicine::orderBy('name')->get(['id', 'name']),
            'notice' => 'These warnings are text you enter. The system does not treat them as clinical advice.',
        ]);
    }

    public function storeClinicalRule(Request $request)
    {
        $this->authorizePermission('manage-medicines');
        $data = $request->validate([
            'medicine_id' => 'nullable|exists:medicines,id',
            'other_medicine_id' => 'nullable|exists:medicines,id',
            'rule_type' => 'required|string|max:32',
            'message' => 'required|string|max:500',
        ]);
        ClinicalRule::create($data + ['is_active' => true]);

        return back()->with('success', 'Rule saved. It appears when that medicine is dispensed.');
    }

    public function setPrescriptionStatus(Request $request, Prescription $prescription)
    {
        $this->authorizePermission('dispense');
        $data = $request->validate([
            'status' => 'required|in:pending,preparing,ready,partial,dispensed,cancelled',
        ]);
        if ($data['status'] === 'cancelled' && $prescription->items()->where('dispensed_quantity', '>', 0)->exists()) {
            return back()->with('error', 'A prescription that has already been dispensed cannot be cancelled.');
        }
        $prescription->update(['status' => $data['status']]);

        return back()->with('success', 'Queue status updated.');
    }

    public function adjust(Request $request, JournalPoster $poster)
    {
        $this->authorizePermission('manage-inventory');
        $data = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'quantity_delta' => 'required|integer|not_in:0',
            'type' => 'required|in:opening,adjustment,damage,write_off',
            'reason' => 'required|string|max:500',
        ]);
        $stock = Stock::findOrFail($data['stock_id']);
        $next = $stock->quantity + $data['quantity_delta'];
        if ($next < 0) {
            throw ValidationException::withMessages(['quantity_delta' => ['That would make the batch negative.']]);
        }
        $oldQty = $stock->quantity;
        DB::transaction(function () use ($data, $stock, $next, $request, $oldQty) {
            $stock->update([
                'quantity' => $next,
                'status' => $data['type'] === 'damage' ? 'damaged' : ($data['type'] === 'write_off' ? 'expired' : $stock->status),
                'is_active' => $next > 0 && !in_array($data['type'], ['damage', 'write_off'], true),
            ]);
            StockAdjustment::create([
                'branch_id' => $stock->branch_id,
                'stock_id' => $stock->id,
                'quantity_delta' => $data['quantity_delta'],
                'type' => $data['type'],
                'reason' => $data['reason'],
                'user_id' => $request->user()->id,
            ]);
            StockTransaction::create([
                'stock_id' => $stock->id,
                'medicine_id' => $stock->medicine_id,
                'type' => $data['type'] === 'write_off' ? 'write_off' : 'adjustment',
                'quantity' => $data['quantity_delta'],
                'unit_price' => $stock->purchase_price,
                'total_amount' => abs($data['quantity_delta']) * (float) $stock->purchase_price,
                'batch_number' => $stock->batch_number,
                'expiry_date' => $stock->expiry_date,
                'notes' => $data['reason'],
                'user_id' => $request->user()->id,
            ]);
            AuditRecorder::record('inventory', $stock, $data['type'], ['quantity' => $oldQty], ['quantity' => $next, 'reason' => $data['reason']]);
        });

        if (in_array($data['type'], ['damage', 'write_off'], true) && $data['quantity_delta'] < 0) {
            $loss = abs($data['quantity_delta']) * (float) $stock->purchase_price;
            $poster->post($stock->branch_id, now()->toDateString(), 'stock_adjustment', $stock->id, $data['reason'], [
                ['code' => '5100', 'debit' => $loss],
                ['code' => '1200', 'credit' => $loss],
            ]);
        }

        return back()->with('success', 'Stock adjustment saved.');
    }

    public function salesReturnForm()
    {
        $this->authorizePermission('manage-invoices');

        return Inertia::render('SalesReturn/Create', [
            'invoices' => Invoice::with('customer', 'items.medicine')->latest()->limit(30)->get(),
        ]);
    }

    public function storeSalesReturn(Request $request, JournalPoster $poster)
    {
        $this->authorizePermission('manage-invoices');
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'invoice_item_id' => 'required|exists:invoice_items,id',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|string|max:32',
            'restock' => 'nullable|boolean',
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        $item = InvoiceItem::where('invoice_id', $data['invoice_id'])->findOrFail($data['invoice_item_id']);
        if ($data['quantity'] > $item->quantity) {
            throw ValidationException::withMessages(['quantity' => ['Return quantity exceeds the sold quantity.']]);
        }
        $amount = $data['quantity'] * (float) $item->rate;
        $invoice = Invoice::findOrFail($data['invoice_id']);

        DB::transaction(function () use ($data, $item, $amount, $invoice, $request) {
            $return = SalesReturn::create([
                'invoice_id' => $invoice->id,
                'branch_id' => $invoice->branch_id,
                'return_date' => $data['return_date'],
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);
            $stock = $item->stock_id ? Stock::find($item->stock_id) : null;
            if ($request->boolean('restock') && $stock) {
                $stock->increment('quantity', $data['quantity']);
                $stock->update(['status' => 'available', 'is_active' => true]);
            } elseif ($stock) {
                $stock->update(['status' => 'quarantine']);
            }
            SalesReturnItem::create([
                'sales_return_id' => $return->id,
                'invoice_item_id' => $item->id,
                'stock_id' => $stock?->id,
                'quantity' => $data['quantity'],
                'condition' => $data['condition'],
                'restock' => $request->boolean('restock'),
                'amount' => $amount,
            ]);
        });

        $unitCost = $item->quantity > 0 ? ((float) $item->cost_amount / $item->quantity) : 0;
        $lines = [
            ['code' => '4100', 'debit' => $amount],
            ['code' => '1000', 'credit' => $amount],
        ];
        if ($request->boolean('restock')) {
            $lines[] = ['code' => '1200', 'debit' => $unitCost * $data['quantity']];
            $lines[] = ['code' => '5000', 'credit' => $unitCost * $data['quantity']];
        }
        $poster->post($invoice->branch_id, $data['return_date'], 'sales_return', $invoice->id, 'Sales return', $lines);

        return back()->with('success', 'Return recorded. Stock goes back to available only when restock is selected.');
    }

    public function prescriptions()
    {
        $this->authorizePermission('dispense');

        return Inertia::render('Prescription/Index', [
            'prescriptions' => Prescription::with('customer', 'items.medicine')->latest()->limit(40)->get(),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'mobile']),
            'medicines' => Medicine::where('status', true)->orderBy('name')->get(['id', 'name', 'generic_id', 'is_controlled', 'strength']),
        ]);
    }

    public function storePrescription(Request $request)
    {
        $this->authorizePermission('dispense');
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'doctor_name' => 'required|string|max:255',
            'diagnosis' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.dose' => 'nullable|string|max:100',
            'items.*.frequency' => 'nullable|string|max:100',
            'items.*.duration' => 'nullable|string|max:100',
            'items.*.route' => 'nullable|string|max:100',
            'items.*.strength' => 'nullable|string|max:100',
            'items.*.substitution_allowed' => 'nullable|boolean',
        ]);
        $rx = Prescription::create([
            'branch_id' => BranchContext::id(),
            'customer_id' => $data['customer_id'],
            'doctor_name' => $data['doctor_name'],
            'diagnosis' => $data['diagnosis'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'user_id' => $request->user()->id,
        ]);
        foreach ($data['items'] as $item) {
            $rx->items()->create([
                'medicine_id' => $item['medicine_id'],
                'quantity' => $item['quantity'],
                'dose' => $item['dose'] ?? null,
                'frequency' => $item['frequency'] ?? null,
                'duration' => $item['duration'] ?? null,
                'route' => $item['route'] ?? null,
                'strength' => $item['strength'] ?? null,
                'substitution_allowed' => !empty($item['substitution_allowed']),
            ]);
        }

        return back()->with('success', 'Prescription queued.');
    }

    public function showPrescription(Prescription $prescription)
    {
        $this->authorizePermission('dispense');
        $prescription->load('customer', 'items.medicine.generic');
        $items = $prescription->items->map(function (PrescriptionItem $item) {
            $medicine = $item->medicine;
            $warnings = ClinicalRule::query()
                ->where('is_active', true)
                ->where(function ($q) use ($medicine) {
                    $q->where('medicine_id', $medicine->id)->orWhere('other_medicine_id', $medicine->id);
                })
                ->pluck('message');
            $alternatives = $medicine->generic_id
                ? Medicine::where('generic_id', $medicine->generic_id)->where('id', '!=', $medicine->id)->where('status', true)->get(['id', 'name', 'strength'])
                : [];

            return [
                'id' => $item->id,
                'medicine_id' => $item->medicine_id,
                'name' => $medicine->name,
                'quantity' => $item->quantity,
                'dispensed_quantity' => $item->dispensed_quantity,
                'dose' => $item->dose,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'substitution_allowed' => $item->substitution_allowed,
                'is_controlled' => (bool) $medicine->is_controlled,
                'warnings' => $warnings,
                'alternatives' => $alternatives,
            ];
        });

        return Inertia::render('Prescription/Show', [
            'prescription' => $prescription,
            'lines' => $items,
            'notice' => 'Substitution and any warning stored for a medicine are pharmacist decisions. This screen does not provide clinical clearance.',
        ]);
    }

    public function dispense(Request $request, Prescription $prescription, FefoAllocator $allocator)
    {
        $this->authorizePermission('dispense');
        $data = $request->validate([
            'prescription_item_id' => 'required|exists:prescription_items,id',
            'quantity' => 'required|integer|min:1',
            'substitute_medicine_id' => 'nullable|exists:medicines,id',
        ]);
        $item = $prescription->items()->findOrFail($data['prescription_item_id']);
        $medicineId = (int) $item->medicine_id;
        if (!empty($data['substitute_medicine_id']) && (int) $data['substitute_medicine_id'] !== $medicineId) {
            if (!$item->substitution_allowed) {
                throw ValidationException::withMessages([
                    'substitute_medicine_id' => ['The prescription does not allow substitution.'],
                ]);
            }
            $medicineId = (int) $data['substitute_medicine_id'];
        }
        $medicine = Medicine::findOrFail($medicineId);

        DB::transaction(function () use ($allocator, $data, $item, $medicine, $medicineId, $prescription, $request) {
            $result = $allocator->allocate($medicineId, (int) $data['quantity'], $prescription->branch_id ?? BranchContext::id());
            if ($result['short'] > 0) {
                throw ValidationException::withMessages(['quantity' => ['Not enough saleable batch quantity for this medicine.']]);
            }
            foreach ($result['lines'] as $line) {
                $stock = $line['stock'];
                $stock->decrement('quantity', $line['quantity']);
                if ($stock->quantity <= 0) {
                    $stock->update(['is_active' => false]);
                }
                if ($medicine->is_controlled || $medicine->is_narcotic) {
                    ControlledDrugRegister::create([
                        'branch_id' => $prescription->branch_id,
                        'customer_id' => $prescription->customer_id,
                        'medicine_id' => $medicine->id,
                        'stock_id' => $stock->id,
                        'prescription_id' => $prescription->id,
                        'quantity' => $line['quantity'],
                        'user_id' => $request->user()->id,
                        'dispensed_at' => now(),
                    ]);
                }
            }
            $item->increment('dispensed_quantity', $data['quantity']);
            $prescription->load('items');
            $pending = $prescription->items->contains(fn ($row) => $row->dispensed_quantity < $row->quantity);
            $any = $prescription->items->contains(fn ($row) => $row->dispensed_quantity > 0);
            $prescription->update(['status' => $pending ? ($any ? 'partial' : 'pending') : 'dispensed']);
            AuditRecorder::record('prescription', $prescription, 'dispense', null, [
                'item' => $item->id,
                'medicine_id' => $medicineId,
                'quantity' => $data['quantity'],
            ]);
        });

        return back()->with('success', 'Dispensed using the earliest usable expiry.');
    }

    public function controlledRegister()
    {
        $this->authorizePermission('manage-controlled');

        return Inertia::render('Compliance/Register', [
            'rows' => ControlledDrugRegister::with('customer', 'medicine', 'user')->latest('dispensed_at')->limit(100)->get(),
        ]);
    }

    public function finance()
    {
        $this->authorizePermission('manage-finance');
        $accounts = LedgerAccount::orderBy('code')->get()->map(function (LedgerAccount $account) {
            $debit = (float) JournalLine::where('ledger_account_id', $account->id)->sum('debit');
            $credit = (float) JournalLine::where('ledger_account_id', $account->id)->sum('credit');
            $balance = in_array($account->type, ['asset', 'expense'], true) ? $debit - $credit : $credit - $debit;

            return ['code' => $account->code, 'name' => $account->name, 'type' => $account->type, 'balance' => round($balance, 2)];
        });
        $sales = (float) ($accounts->firstWhere('code', '4000')['balance'] ?? 0);
        $returns = (float) ($accounts->firstWhere('code', '4100')['balance'] ?? 0);
        $cogs = (float) ($accounts->firstWhere('code', '5000')['balance'] ?? 0);

        return Inertia::render('Finance/Index', [
            'accounts' => $accounts,
            'profit' => round($sales - $returns - $cogs, 2),
            'openInvoices' => Invoice::with('customer')->where('due_amount', '>', 0)->latest()->limit(30)->get(['id', 'invoice_no', 'customer_id', 'due_amount']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function audit()
    {
        $this->authorizePermission('view-audit');

        return Inertia::render('Audit/Index', [
            'logs' => AuditLog::with('user')->latest()->limit(100)->get(),
        ]);
    }

    private function nameCatalog(string $model, string $title, string $routeName)
    {
        $this->authorizePermission('manage-medicines');

        return Inertia::render('Catalog/Index', [
            'title' => $title,
            'rows' => $model::withCount('medicines')->orderBy('name')->get(),
            'storeRoute' => $routeName.'.store',
            'updateRoute' => $routeName.'.update',
            'destroyRoute' => $routeName.'.destroy',
        ]);
    }

    private function storeName(string $model, Request $request, string $table, string $label)
    {
        $this->authorizePermission('manage-medicines');
        $data = $request->validate(['name' => 'required|string|max:255|unique:'.$table.',name']);
        $model::create($data);

        return back()->with('success', $label.' saved.');
    }

    private function updateName($row, Request $request, string $table, string $label)
    {
        $this->authorizePermission('manage-medicines');
        $data = $request->validate(['name' => 'required|string|max:255|unique:'.$table.',name,'.$row->id]);
        $row->update($data);

        return back()->with('success', $label.' updated.');
    }

    private function destroyName($row, string $label)
    {
        $this->authorizePermission('manage-medicines');
        if (method_exists($row, 'medicines') && $row->medicines()->exists()) {
            return back()->with('error', 'Cannot delete this '.$label.' while medicines use it.');
        }
        $row->delete();

        return back()->with('success', ucfirst($label).' deleted.');
    }

    private function authorizePermission(string $permission): void
    {
        if (!auth()->check()) {
            abort(403);
        }
    }
}
