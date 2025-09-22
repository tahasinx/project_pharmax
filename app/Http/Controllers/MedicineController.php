<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MedicineController extends Controller
{
    public function index()
    {
        $medicines     = Medicine::with(['category', 'manufacturer'])->paginate(15);
        $categories    = Category::where('status', true)->get();
        $manufacturers = Manufacturer::where('status', true)->get();

        return Inertia::render('Medicine/Index', [
            'medicines'     => $medicines,
            'categories'    => $categories,
            'manufacturers' => $manufacturers,
        ]);
    }

    public function create()
    {
        $categories = Category::where('status', true)->get();
        $manufacturers = Manufacturer::where('status', true)->get();

        return Inertia::render('Medicine/Create', [
            'categories' => $categories,
            'manufacturers' => $manufacturers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'price' => 'required|numeric|min:0',
            'manufacturer_price' => 'required|numeric|min:0',
            'box_size' => 'required|integer|min:1',
        ]);

        $medicine = Medicine::create([
            'product_id'        => $this->generateProductId(),
            'name'              => $request->name,
            'category_id'       => $request->category_id,
            'manufacturer_id'   => $request->manufacturer_id,
            'generic_name'      => $request->generic_name,
            'strength'          => $request->strength,
            'box_size'          => $request->box_size,
            'product_location'  => $request->product_location,
            'price'             => $request->price,
            'manufacturer_price' => $request->manufacturer_price,
            'unit'              => $request->unit,
            'details'           => $request->details,
            'status'            => $request->status ?? true,
        ]);

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine created successfully.');
    }

    public function show(Medicine $medicine)
    {
        $medicine->load(['category', 'manufacturer']);

        return Inertia::render('Medicine/Show', [
            'medicine' => $medicine,
        ]);
    }

    public function edit(Medicine $medicine)
    {
        $categories = Category::where('status', true)->get();
        $manufacturers = Manufacturer::where('status', true)->get();

        return Inertia::render('Medicine/Edit', [
            'medicine' => $medicine,
            'categories' => $categories,
            'manufacturers' => $manufacturers,
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'price' => 'required|numeric|min:0',
            'manufacturer_price' => 'required|numeric|min:0',
            'box_size' => 'required|integer|min:1',
        ]);

        $medicine->update($request->all());

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        try {
            Excel::import(new MedicineImport, $request->file('file'));

            return redirect()->route('medicines.index')
                ->with('success', 'Medicines imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('medicines.index')
                ->with('error', 'Error importing medicines: ' . $e->getMessage());
        }
    }

    public function generateBarcode(Medicine $medicine)
    {
        return Inertia::render('Medicine/Barcode', [
            'medicine' => $medicine,
        ]);
    }

    public function generateQrCode(Medicine $medicine)
    {
        return Inertia::render('Medicine/QrCode', [
            'medicine' => $medicine,
        ]);
    }

    private function generateProductId()
    {
        do {
            $productId = Str::random(8);
        } while (Medicine::where('product_id', $productId)->exists());

        return $productId;
    }
}
