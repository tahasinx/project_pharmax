<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MedicineImport;

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

        // Auto-generate QR code and barcode for new medicine
        $this->generateDefaultCodes($medicine);

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

        // Auto-generate codes if they don't exist
        if (!$medicine->qr_code_data || !$medicine->barcode_data) {
            $this->generateDefaultCodes($medicine);
        }

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
            'file' => 'required|file|max:5120|mimetypes:text/csv,text/plain,application/vnd.ms-excel,application/csv',
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


    public function generateCodes(Medicine $medicine)
    {
        $medicine->load(['category', 'manufacturer']);

        return Inertia::render('Medicine/Codes', [
            'medicine' => $medicine,
        ]);
    }


    public function saveCodes(Request $request, Medicine $medicine)
    {
        $request->validate([
            'qr_code_data' => 'nullable|string',
            'qr_code_type' => 'nullable|string|in:product_id,medicine_info,custom',
            'qr_code_image_path' => 'nullable|string',
            'barcode_data' => 'nullable|string',
            'barcode_type' => 'nullable|string|in:code128,code39,ean13,upc',
            'barcode_image_path' => 'nullable|string',
        ]);

        $updateData = [];

        if ($request->has('qr_code_data')) {
            $updateData['qr_code_data'] = $request->qr_code_data;
            $updateData['qr_code_type'] = $request->qr_code_type;
            $updateData['qr_code_image_path'] = $request->qr_code_image_path;
        }

        if ($request->has('barcode_data')) {
            $updateData['barcode_data'] = $request->barcode_data;
            $updateData['barcode_type'] = $request->barcode_type;
            $updateData['barcode_image_path'] = $request->barcode_image_path;
        }

        $medicine->update($updateData);

        return redirect()->back()->with('success', 'Codes saved successfully.');
    }

    private function generateProductId()
    {
        do {
            $productId = Str::random(8);
        } while (Medicine::where('product_id', $productId)->exists());

        return $productId;
    }

    private function generateDefaultCodes(Medicine $medicine)
    {
        // Generate QR code data (using product ID)
        $qrCodeData = $medicine->product_id;

        // Generate barcode data (using product ID)
        $barcodeData = $medicine->product_id;

        // Update medicine with generated codes
        $medicine->update([
            'qr_code_data' => $qrCodeData,
            'qr_code_type' => 'product_id',
            'barcode_data' => $barcodeData,
            'barcode_type' => 'code128',
        ]);
    }
}
