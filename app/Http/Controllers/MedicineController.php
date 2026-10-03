<?php

namespace App\Http\Controllers;

use App\Domain\Audit\AuditRecorder;
use App\Domain\Medex\MedexCatalogService;
use App\Imports\MedicineImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DosageForm;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\Unit;
use App\Traits\HasSettingsPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MedicineController extends Controller
{
    use HasSettingsPagination;

    public function index(Request $request)
    {
        $itemsPerPage = $this->getItemsPerPage();
        $brandId        = Brand::localId($request->get('brand_id'));
        $genericId      = Generic::localId($request->get('generic_id'));
        $manufacturerId = Manufacturer::localId($request->get('manufacturer_id'));
        $priceMin       = $request->filled('price_min') ? (float) $request->get('price_min') : null;
        $priceMax       = $request->filled('price_max') ? (float) $request->get('price_max') : null;
        $status         = $request->get('status');

        $medicines = Medicine::with(['category', 'manufacturer', 'medicineType', 'dosageForm', 'generic', 'brand'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('dosage_form', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('manufacturer', fn ($manufacturer) => $manufacturer->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('generic', fn ($generic) => $generic->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($brandId, fn ($query) => $query->where('brand_id', $brandId))
            ->when($genericId, fn ($query) => $query->where('generic_id', $genericId))
            ->when($manufacturerId, fn ($query) => $query->where('manufacturer_id', $manufacturerId))
            ->when($request->filled('segment'), function ($query) use ($request) {
                $segment = $request->get('segment');
                $query->whereHas('medicineType', function ($type) use ($segment) {
                    $type->where('name', $segment === 'herbal' ? 'Herbal' : ($segment === 'device' ? 'Device' : 'Allopathic'));
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('status', true))
            ->when($status === 'inactive', fn ($query) => $query->where('status', false))
            ->when($priceMin !== null, fn ($query) => $query->where('price', '>=', $priceMin))
            ->when($priceMax !== null, fn ($query) => $query->where('price', '<=', $priceMax))
            ->orderBy('name')
            ->paginate($itemsPerPage)
            ->withQueryString();
        $categories    = Category::where('status', true)->get();
        $manufacturers = Manufacturer::where('status', true)->orderBy('name')->get(['id', 'manufacturer_id', 'name']);
        $brands        = Brand::query()
            ->where(fn ($q) => $q->where('is_active', true)->orWhereNull('is_active'))
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'brand_id', 'name']);
        $dosageForms   = DosageForm::where('is_active', true)->orderBy('name')->get(['id', 'dosage_form_id', 'name']);
        $generics      = Generic::where('is_active', true)->orderBy('name')->limit(500)->get(['id', 'generic_id', 'name', 'segment']);

        return Inertia::render('Medicine/Index', [
            'medicines'     => $medicines,
            'categories'    => $categories,
            'manufacturers' => $manufacturers,
            'brands'        => $brands,
            'dosageForms'   => $dosageForms,
            'generics'      => $generics,
            'filters'       => [
                'search'          => (string) $request->get('search', ''),
                'segment'         => (string) $request->get('segment', ''),
                'brand_id'        => (string) $request->get('brand_id', ''),
                'generic_id'      => (string) $request->get('generic_id', ''),
                'manufacturer_id' => (string) $request->get('manufacturer_id', ''),
                'status'          => (string) $request->get('status', ''),
                'price_min'       => $request->filled('price_min') ? (string) $request->get('price_min') : '',
                'price_max'       => $request->filled('price_max') ? (string) $request->get('price_max') : '',
            ],
        ]);
    }

    public function create()
    {
        $categories    = Category::where('status', true)->get();
        $manufacturers = Manufacturer::where('status', true)->get();

        return Inertia::render('Medicine/Create', [
            'categories'    => $categories,
            'manufacturers' => $manufacturers,
            'generics'      => Generic::orderBy('name')->get(),
            'brands'        => Brand::orderBy('name')->get(),
            'medicineTypes' => MedicineType::whereIn('name', ['Allopathic', 'Herbal', 'Device'])->orderBy('name')->get(),
            'dosageForms'   => DosageForm::where('is_active', true)->orderBy('name')->get(),
            'units'         => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'category_id'        => 'nullable|exists:categories,category_id',
            'manufacturer_id'    => 'nullable|exists:manufacturers,manufacturer_id',
            'medicine_type_id'   => 'required|exists:medicine_types,medicine_type_id',
            'price'              => 'required|numeric|min:0',
            'manufacturer_price' => 'required|numeric|min:0',
            'box_size'           => 'nullable|integer|min:1',
            'discount_percent'   => 'nullable|numeric|min:0|max:100',
            'alert_qty'          => 'nullable|integer|min:0',
            'barcode'            => 'nullable|string|max:255',
            'image'              => 'nullable|image|max:2048',
            'generic_id'         => 'required|exists:generics,generic_id',
            'strength'           => 'required|string|max:255',
            'unit'               => 'required|string|max:255',
            'dosage_form_id'     => 'nullable|exists:dosage_forms,dosage_form_id',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('medicines', 'public')
            : null;

        $generic = Generic::findByPublicId($request->generic_id);
        $dosageForm = DosageForm::findByPublicId($request->dosage_form_id);

        $medicine = Medicine::create([
            'product_id'            => $this->generateProductId(),
            'name'                  => $request->name,
            'category_id'           => Category::localId($request->category_id),
            'manufacturer_id'       => Manufacturer::localId($request->manufacturer_id),
            'generic_name'          => $generic?->name ?? $request->generic_name,
            'generic_id'            => $generic?->id,
            'medicine_type_id'      => MedicineType::localIdOrFail($request->medicine_type_id),
            'brand_id'              => Brand::localId($request->brand_id),
            'strength'              => $request->strength,
            'dosage_form_id'        => $dosageForm?->id,
            'dosage_form'           => $dosageForm?->name ?? $request->dosage_form,
            'atc_code'              => $request->atc_code,
            'sku'                   => $request->sku,
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_controlled'         => $request->boolean('is_controlled'),
            'is_antibiotic'         => $request->boolean('is_antibiotic'),
            'is_high_risk'          => $request->boolean('is_high_risk'),
            'is_refrigerated'       => $request->boolean('is_refrigerated'),
            'is_narcotic'           => $request->boolean('is_narcotic'),
            'box_size'              => $request->input('box_size', 1) ?: 1,
            'product_location'      => $request->product_location,
            'price'                 => $request->price,
            'discount_percent'      => $request->input('discount_percent', 0),
            'manufacturer_price'    => $request->manufacturer_price,
            'unit'                  => $request->unit,
            'alert_qty'             => $request->input('alert_qty', 0),
            'barcode_data'          => $request->barcode,
            'barcode_type'          => 'code128',
            'image'                 => $imagePath,
            'details'               => $request->details,
            'status'                => $request->has('status') ? $request->boolean('status') : true,
        ]);

        $this->generateDefaultCodes($medicine);
        $this->syncUnits($medicine, $request->input('units', []));

        if ($request->boolean('manage_stock')) {
            return redirect()->route('stocks.create', ['medicine' => $medicine->getRouteKey()])
                ->with('success', 'Medicine created. Add its stock.');
        }

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine created successfully.');
    }

    public function show(Medicine $medicine)
    {
        $medicine->load(['category', 'manufacturer', 'medicineType', 'dosageForm', 'brand'])
            ->loadCount(['purchaseItems', 'invoiceItems']);

        return Inertia::render('Medicine/Show', [
            'medicine' => $medicine,
        ]);
    }

    public function edit(Medicine $medicine)
    {
        $categories    = Category::where('status', true)->get();
        $manufacturers = Manufacturer::where('status', true)->get();

        $medicine->load(['units', 'category', 'manufacturer', 'generic', 'medicineType', 'brand', 'dosageForm']);

        $payload                     = $medicine->toArray();
        $payload['category_id']      = $medicine->category?->publicId();
        $payload['manufacturer_id']  = $medicine->manufacturer?->publicId();
        $payload['generic_id']       = $medicine->generic?->publicId();
        $payload['medicine_type_id'] = $medicine->medicineType?->publicId();
        $payload['brand_id']         = $medicine->brand?->publicId();
        $payload['dosage_form_id']   = $medicine->dosageForm?->publicId();

        return Inertia::render('Medicine/Edit', [
            'medicine'      => $payload,
            'categories'    => $categories,
            'manufacturers' => $manufacturers,
            'generics'      => Generic::orderBy('name')->get(),
            'brands'        => Brand::orderBy('name')->get(),
            'medicineTypes' => MedicineType::whereIn('name', ['Allopathic', 'Herbal', 'Device'])->orderBy('name')->get(),
            'dosageForms'   => DosageForm::where('is_active', true)->orderBy('name')->get(),
            'units'         => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'category_id'        => 'nullable|exists:categories,category_id',
            'manufacturer_id'    => 'nullable|exists:manufacturers,manufacturer_id',
            'medicine_type_id'   => 'required|exists:medicine_types,medicine_type_id',
            'generic_id'         => 'required|exists:generics,generic_id',
            'strength'           => 'required|string|max:255',
            'unit'               => 'required|string|max:255',
            'price'              => 'required|numeric|min:0',
            'manufacturer_price' => 'required|numeric|min:0',
            'box_size'           => 'nullable|integer|min:1',
            'discount_percent'   => 'nullable|numeric|min:0|max:100',
            'alert_qty'          => 'nullable|integer|min:0',
            'barcode'            => 'nullable|string|max:255',
            'image'              => 'nullable|image|max:2048',
            'dosage_form_id'     => 'nullable|exists:dosage_forms,dosage_form_id',
        ]);

        $oldPrice = $medicine->price;
        $generic  = Generic::findByPublicId($request->generic_id);
        $dosageForm = DosageForm::findByPublicId($request->dosage_form_id);
        $data     = [
            'name'                  => $request->name,
            'category_id'           => Category::localId($request->category_id),
            'manufacturer_id'       => Manufacturer::localId($request->manufacturer_id),
            'generic_name'          => $generic?->name,
            'generic_id'            => $generic?->id,
            'medicine_type_id'      => MedicineType::localIdOrFail($request->medicine_type_id),
            'dosage_form_id'        => $dosageForm?->id,
            'dosage_form'           => $dosageForm?->name ?? $medicine->dosage_form,
            'strength'              => $request->strength,
            'price'                 => $request->price,
            'discount_percent'      => $request->input('discount_percent', 0),
            'manufacturer_price'    => $request->manufacturer_price,
            'box_size'              => $request->input('box_size', $medicine->box_size) ?: 1,
            'unit'                  => $request->unit,
            'alert_qty'             => $request->input('alert_qty', 0),
            'product_location'      => $request->product_location,
            'details'               => $request->details,
            'barcode_data'          => $request->barcode ?: $medicine->barcode_data,
            'status'                => $request->has('status') ? $request->boolean('status') : $medicine->status,
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_controlled'         => $request->boolean('is_controlled'),
            'is_narcotic'           => $request->boolean('is_narcotic'),
            'is_antibiotic'         => $request->boolean('is_antibiotic'),
            'is_high_risk'          => $request->boolean('is_high_risk'),
            'is_refrigerated'       => $request->boolean('is_refrigerated'),
        ];
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('medicines', 'public');
        }
        $medicine->update($data);
        if ($request->exists('units')) {
            $this->syncUnits($medicine, $request->input('units', []));
        }
        if ((float) $oldPrice !== (float) $medicine->price) {
            AuditRecorder::record('medicine', $medicine, 'price', ['price' => $oldPrice], ['price' => $medicine->price]);
        }

        // Auto-generate codes if they don't exist
        if (! $medicine->qr_code_data || ! $medicine->barcode_data) {
            $this->generateDefaultCodes($medicine);
        }

        if ($request->boolean('manage_stock')) {
            return redirect()->route('stocks.create', ['medicine' => $medicine->getRouteKey()])
                ->with('success', 'Medicine updated. Add its stock.');
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
                ->with('error', 'Error importing medicines: '.$e->getMessage());
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
            'qr_code_data'       => 'nullable|string',
            'qr_code_type'       => 'nullable|string|in:product_id,medicine_info,custom',
            'qr_code_image_path' => 'nullable|string',
            'qr_code_size'       => 'nullable|integer|min:96|max:360',
            'barcode_data'       => 'nullable|string',
            'barcode_type'       => 'nullable|string|in:code128,code39,ean13,upc',
            'barcode_image_path' => 'nullable|string',
            'barcode_size'       => 'nullable|integer|min:48|max:200',
        ]);

        $updateData = [];

        if ($request->has('qr_code_data')) {
            $updateData['qr_code_data']       = $request->qr_code_data;
            $updateData['qr_code_type']       = $request->qr_code_type;
            $updateData['qr_code_image_path'] = $request->qr_code_image_path;
        }

        if ($request->filled('qr_code_size')) {
            $updateData['qr_code_size'] = (int) $request->qr_code_size;
        }

        if ($request->has('barcode_data')) {
            $updateData['barcode_data']       = $request->barcode_data;
            $updateData['barcode_type']       = $request->barcode_type;
            $updateData['barcode_image_path'] = $request->barcode_image_path;
        }

        if ($request->filled('barcode_size')) {
            $updateData['barcode_size'] = (int) $request->barcode_size;
        }

        $medicine->update($updateData);

        return redirect()->back()->with('success', 'Codes saved successfully.');
    }

    public function storeExternal(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'category'     => 'nullable|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strength'     => 'nullable|string|max:255',
            'dosage_form'  => 'nullable|string|max:255',
            'price'        => 'nullable|numeric|min:0',
            'medex_id'     => 'nullable|string|max:255',
            'medex_name'   => 'nullable|string|max:255',
            'segment'      => 'nullable|in:allopathic,herbal,device',
            'details'      => 'nullable|array',
        ]);

        /** @var MedexCatalogService $medex */
        $medex = app(MedexCatalogService::class);

        $medicineName = trim((string) $request->name);
        $manufacturerName = trim((string) $request->manufacturer);
        $genericName = trim((string) ($request->generic_name ?: ''));
        $categoryName = trim((string) ($request->category ?: ''));
        $dosageFormName = trim((string) ($request->dosage_form ?: ''));
        $strength = trim((string) ($request->strength ?: ''));
        $segment = (string) ($request->input('segment') ?: 'allopathic');

        $manufacturer = $medex->firstOrCreateByName(Manufacturer::class, $manufacturerName, [
            'status'  => true,
            'segment' => $segment,
        ]);

        $category = $categoryName !== ''
            ? $medex->firstOrCreateByName(Category::class, $categoryName, ['status' => true])
            : null;

        $generic = $genericName !== ''
            ? $medex->firstOrCreateByName(Generic::class, $genericName, [
                'is_active' => true,
                'segment'   => $segment,
            ])
            : null;

        $brandName = $medex->brandFamilyName($medicineName, $strength, $dosageFormName);
        $brand = $medex->firstOrCreateByName(Brand::class, $brandName, [
            'is_active' => true,
            'segment'   => $segment,
        ]);

        $dosageForm = $medex->resolveDosageForm($dosageFormName !== '' ? $dosageFormName : null);
        $medicineType = $medex->resolveSegmentType($segment);

        $reference = null;
        if ($request->filled('details') && is_array($request->input('details'))) {
            $reference = [
                'source' => 'MedEx',
                'sections' => collect($request->input('details'))
                    ->only(['indications', 'pharmacology', 'interaction', 'contraindications', 'side_effects', 'pregnancy', 'precautions', 'dosage', 'storage'])
                    ->filter(fn ($value) => filled($value))
                    ->map(function ($value) {
                        if (! is_string($value)) {
                            return $value;
                        }
                        $clean = strip_tags($value, '<p><br><ul><ol><li><strong><em><b><i>');

                        return trim($clean) !== '' ? trim($clean) : null;
                    })
                    ->filter()
                    ->all(),
            ];
        }

        $existing = null;
        if ($request->filled('medex_id')) {
            $existing = Medicine::query()->where('medex_id', $request->medex_id)->first();
        }
        if (! $existing) {
            $existing = Medicine::query()
                ->where('name', $medicineName)
                ->where('manufacturer_id', $manufacturer->id)
                ->first();
        }
        if ($existing) {
            $existing->fill([
                'generic_id'       => $existing->generic_id ?: $generic?->id,
                'brand_id'         => $existing->brand_id ?: $brand->id,
                'category_id'      => $existing->category_id ?: $category?->id,
                'medicine_type_id' => $existing->medicine_type_id ?: $medicineType->id,
                'dosage_form_id'   => $existing->dosage_form_id ?: $dosageForm?->id,
                'dosage_form'      => $existing->dosage_form ?: ($dosageFormName !== '' ? $dosageFormName : null),
                'generic_name'     => $existing->generic_name ?: ($genericName !== '' ? $genericName : null),
                'medex_id'         => $request->medex_id ?: $existing->medex_id,
                'medex_name'       => $request->medex_name ?: $existing->medex_name,
                'details'          => $reference ? json_encode($reference) : $existing->details,
            ]);
            $existing->save();

            return response()->json([
                'status' => 'duplicate',
                'id'     => $existing->publicId() ?: $existing->id,
                'created' => [
                    'manufacturer' => $manufacturer->name,
                    'brand'        => $brand->name,
                    'generic'      => $generic?->name,
                    'dosage_form'  => $dosageForm?->name,
                    'segment'      => $medicineType->name,
                ],
            ], 200);
        }

        do {
            $productId = Str::random(8);
        } while (Medicine::where('product_id', $productId)->exists());

        $medicine = Medicine::create([
            'product_id'         => $productId,
            'name'               => $medicineName,
            'category_id'        => $category?->id,
            'manufacturer_id'    => $manufacturer->id,
            'generic_id'         => $generic?->id,
            'brand_id'           => $brand->id,
            'medicine_type_id'   => $medicineType->id,
            'dosage_form_id'     => $dosageForm?->id,
            'generic_name'       => $genericName !== '' ? $genericName : null,
            'strength'           => $strength !== '' ? $strength : null,
            'dosage_form'        => $dosageFormName !== '' ? $dosageFormName : null,
            'price'              => $request->price ?? 0,
            'manufacturer_price' => 0,
            'box_size'           => 1,
            'unit'               => 'Piece',
            'status'             => true,
            'details'            => $reference ? json_encode($reference) : null,
            'medex_id'           => $request->medex_id,
            'medex_name'         => $request->medex_name,
        ]);

        $this->generateDefaultCodes($medicine);
        $this->syncUnits($medicine, []);

        return response()->json([
            'status'  => 'created',
            'id'      => $medicine->publicId() ?: $medicine->id,
            'created' => [
                'manufacturer' => $manufacturer->name,
                'brand'        => $brand->name,
                'generic'      => $generic?->name,
                'dosage_form'  => $dosageForm?->name,
                'segment'      => $medicineType->name,
            ],
        ]);
    }

    /**
     * Case-insensitive name lookup, then create if missing.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  array<string, mixed>  $attributes
     */
    private function firstOrCreateByName(string $model, string $name, array $attributes = [])
    {
        $name = trim($name);
        $existing = $model::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if ($existing) {
            return $existing;
        }

        return $model::create(array_merge(['name' => $name], $attributes));
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
            'barcode_data' => $medicine->barcode_data ?: $barcodeData,
            'barcode_type' => 'code128',
        ]);
    }

    private function syncUnits(Medicine $medicine, array $units): void
    {
        if ($units === []) {
            $medicine->units()->create(['name' => 'Piece', 'factor_to_base' => 1, 'sort' => 0]);

            return;
        }
        $medicine->units()->delete();
        foreach (array_values($units) as $index => $unit) {
            if (empty($unit['name'])) {
                continue;
            }
            $medicine->units()->create([
                'name'           => $unit['name'],
                'factor_to_base' => max(1, (int) ($unit['factor_to_base'] ?? 1)),
                'sort'           => $index,
            ]);
        }
    }
}
