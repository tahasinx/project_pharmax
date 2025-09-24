<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:manage-medicines');
    }

    public function index()
    {
        $stocks = Stock::with('medicine.category', 'medicine.manufacturer')
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $alerts = $this->getStockAlerts();

        return Inertia::render('Stock/Index', [
            'stocks' => $stocks,
            'alerts' => $alerts,
        ]);
    }

    public function create()
    {
        $medicines = Medicine::with('category', 'manufacturer')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Stock/Create', [
            'medicines' => $medicines,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id'        => 'required|exists:medicines,id',
            'batch_number'        => 'nullable|string|max:100',
            'expiry_date'         => 'required|date|after:today',
            'quantity'            => 'required|integer|min:0',
            'min_stock_level'     => 'required|integer|min:0',
            'max_stock_level'     => 'nullable|integer|min:0',
            'purchase_price'       => 'nullable|numeric|min:0',
            'selling_price'       => 'nullable|numeric|min:0',
            'supplier'            => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        Stock::create([
            'medicine_id'        => $request->medicine_id,
            'batch_number'        => $request->batch_number,
            'expiry_date'         => $request->expiry_date,
            'quantity'            => $request->quantity,
            'min_stock_level'     => $request->min_stock_level,
            'max_stock_level'     => $request->max_stock_level,
            'purchase_price'      => $request->purchase_price,
            'selling_price'       => $request->selling_price,
            'supplier'            => $request->supplier,
            'notes'               => $request->notes,
            'is_active'           => $request->is_active ?? true,
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock added successfully.');
    }

    public function show(Stock $stock)
    {
        $stock->load('medicine.category', 'medicine.manufacturer');

        return Inertia::render('Stock/Show', [
            'stock' => $stock,
        ]);
    }

    public function edit(Stock $stock)
    {
        $stock->load('medicine.category', 'medicine.manufacturer');

        $medicines = Medicine::with('category', 'manufacturer')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Stock/Edit', [
            'stock' => $stock,
            'medicines' => $medicines,
        ]);
    }

    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'medicine_id'        => 'required|exists:medicines,id',
            'batch_number'        => 'nullable|string|max:100',
            'expiry_date'         => 'required|date|after:today',
            'quantity'            => 'required|integer|min:0',
            'min_stock_level'     => 'required|integer|min:0',
            'max_stock_level'     => 'nullable|integer|min:0',
            'purchase_price'      => 'nullable|numeric|min:0',
            'selling_price'       => 'nullable|numeric|min:0',
            'supplier'            => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        $stock->update([
            'medicine_id'        => $request->medicine_id,
            'batch_number'        => $request->batch_number,
            'expiry_date'         => $request->expiry_date,
            'quantity'            => $request->quantity,
            'min_stock_level'     => $request->min_stock_level,
            'max_stock_level'     => $request->max_stock_level,
            'purchase_price'      => $request->purchase_price,
            'selling_price'       => $request->selling_price,
            'supplier'            => $request->supplier,
            'notes'               => $request->notes,
            'is_active'           => $request->is_active ?? $stock->is_active,
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock updated successfully.');
    }

    public function destroy(Stock $stock)
    {
        $stock->update(['is_active' => false]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock deactivated successfully.');
    }

    public function reports()
    {
        $lowStockCount = Stock::lowStock()->count();
        $expiredCount = Stock::expired()->count();
        $expiringSoonCount = Stock::expiringSoon()->count();

        $totalStockValue = Stock::active()->sum(DB::raw('quantity * purchase_price'));
        $totalMedicines = Medicine::where('status', true)->count();

        $stockByCategory = Medicine::with('stocks')
            ->where('status', true)
            ->get()
            ->groupBy('category.name')
            ->map(function ($medicines) {
                return $medicines->sum(function ($medicine) {
                    return $medicine->stocks->where('is_active', true)->sum('quantity');
                });
            });

        $expiringMedicines = Stock::with('medicine')
            ->expiringSoon(30)
            ->orderBy('expiry_date')
            ->get();

        return Inertia::render('Stock/Reports', [
            'stats' => [
                'low_stock_count' => $lowStockCount,
                'expired_count' => $expiredCount,
                'expiring_soon_count' => $expiringSoonCount,
                'total_stock_value' => $totalStockValue,
                'total_medicines' => $totalMedicines,
            ],
            'stockByCategory' => $stockByCategory,
            'expiringMedicines' => $expiringMedicines,
        ]);
    }

    public function alerts()
    {
        $lowStock = Stock::with('medicine.category', 'medicine.manufacturer')
            ->lowStock()
            ->get();

        $expired = Stock::with('medicine.category', 'medicine.manufacturer')
            ->expired()
            ->get();

        $expiringSoon = Stock::with('medicine.category', 'medicine.manufacturer')
            ->expiringSoon(30)
            ->orderBy('expiry_date')
            ->get();

        return Inertia::render('Stock/Alerts', [
            'lowStock' => $lowStock,
            'expired' => $expired,
            'expiringSoon' => $expiringSoon,
        ]);
    }

    private function getStockAlerts()
    {
        return [
            'low_stock' => Stock::with('medicine')->lowStock()->count(),
            'expired' => Stock::with('medicine')->expired()->count(),
            'expiring_soon' => Stock::with('medicine')->expiringSoon(30)->count(),
        ];
    }
}
