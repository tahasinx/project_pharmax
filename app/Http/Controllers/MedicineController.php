<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
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
use Symfony\Component\DomCrawler\Crawler;
use App\Imports\MedicineImport;

class MedicineController extends Controller
{
    use HasSettingsPagination;

    public function index(Request $request)
    {
        $itemsPerPage = $this->getItemsPerPage();
        $medicines = Medicine::with(['category', 'manufacturer', 'medicineType'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('manufacturer', fn ($manufacturer) => $manufacturer->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate($itemsPerPage)
            ->withQueryString();
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
            'generics' => Generic::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'medicineTypes' => MedicineType::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'price' => 'required|numeric|min:0',
            'manufacturer_price' => 'required|numeric|min:0',
            'box_size' => 'nullable|integer|min:1',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'alert_qty' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'generic_id' => 'required|exists:generics,id',
            'strength' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('medicines', 'public')
            : null;

        $medicine = Medicine::create([
            'product_id'        => $this->generateProductId(),
            'name'              => $request->name,
            'category_id'       => $request->category_id ?: null,
            'manufacturer_id'   => $request->manufacturer_id ?: null,
            'generic_name'      => $request->generic_id ? Generic::find($request->generic_id)?->name : $request->generic_name,
            'generic_id'        => $request->generic_id,
            'medicine_type_id'  => $request->medicine_type_id,
            'brand_id'          => $request->brand_id,
            'strength'          => $request->strength,
            'dosage_form'       => $request->dosage_form,
            'atc_code'          => $request->atc_code,
            'sku'               => $request->sku,
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_controlled'     => $request->boolean('is_controlled'),
            'is_antibiotic'     => $request->boolean('is_antibiotic'),
            'is_high_risk'      => $request->boolean('is_high_risk'),
            'is_refrigerated'   => $request->boolean('is_refrigerated'),
            'is_narcotic'       => $request->boolean('is_narcotic'),
            'box_size'          => $request->input('box_size', 1) ?: 1,
            'product_location'  => $request->product_location,
            'price'             => $request->price,
            'discount_percent'  => $request->input('discount_percent', 0),
            'manufacturer_price' => $request->manufacturer_price,
            'unit'              => $request->unit,
            'alert_qty'         => $request->input('alert_qty', 0),
            'barcode_data'      => $request->barcode,
            'barcode_type'      => 'code128',
            'image'             => $imagePath,
            'details'           => $request->details,
            'status'            => $request->has('status') ? $request->boolean('status') : true,
        ]);

        $this->generateDefaultCodes($medicine);
        $this->syncUnits($medicine, $request->input('units', []));

        if ($request->boolean('manage_stock')) {
            return redirect()->route('stocks.create', ['medicine' => $medicine->id])
                ->with('success', 'Medicine created. Add its stock.');
        }

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

        $medicine->load('units');

        return Inertia::render('Medicine/Edit', [
            'medicine' => $medicine,
            'categories' => $categories,
            'manufacturers' => $manufacturers,
            'generics' => Generic::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'medicineTypes' => MedicineType::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'generic_id' => 'required|exists:generics,id',
            'strength' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'manufacturer_price' => 'required|numeric|min:0',
            'box_size' => 'nullable|integer|min:1',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'alert_qty' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $oldPrice = $medicine->price;
        $data = [
            'name' => $request->name,
            'category_id' => $request->category_id ?: null,
            'manufacturer_id' => $request->manufacturer_id ?: null,
            'generic_name' => Generic::find($request->generic_id)?->name,
            'generic_id' => $request->generic_id,
            'medicine_type_id' => $request->medicine_type_id,
            'strength' => $request->strength,
            'price' => $request->price,
            'discount_percent' => $request->input('discount_percent', 0),
            'manufacturer_price' => $request->manufacturer_price,
            'box_size' => $request->input('box_size', $medicine->box_size) ?: 1,
            'unit' => $request->unit,
            'alert_qty' => $request->input('alert_qty', 0),
            'product_location' => $request->product_location,
            'details' => $request->details,
            'barcode_data' => $request->barcode ?: $medicine->barcode_data,
            'status' => $request->has('status') ? $request->boolean('status') : $medicine->status,
        ];
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('medicines', 'public');
        }
        $medicine->update($data);
        if ($request->exists('units')) {
            $this->syncUnits($medicine, $request->input('units', []));
        }
        if ((float) $oldPrice !== (float) $medicine->price) {
            \App\Domain\Audit\AuditRecorder::record('medicine', $medicine, 'price', ['price' => $oldPrice], ['price' => $medicine->price]);
        }

        // Auto-generate codes if they don't exist
        if (!$medicine->qr_code_data || !$medicine->barcode_data) {
            $this->generateDefaultCodes($medicine);
        }

        if ($request->boolean('manage_stock')) {
            return redirect()->route('stocks.create', ['medicine' => $medicine->id])
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

    // MedEx proxy: search
    public function medexSearch(Request $request)
    {
        $searchKey = $request->query('q');
        $url = 'https://medex.com.bd/ajax/search?searchtype=search&searchkey=' . urlencode($searchKey);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200 || !$html) {
            return response()->json(['error' => 'Failed to fetch data from MedEx'], $httpCode);
        }

        $crawler = new Crawler($html);
        $abs = function ($maybe) {
            if (!$maybe) return null;
            if (str_starts_with($maybe, 'http')) return $maybe;
            return 'https://medex.com.bd' . $maybe;
        };
        $results = [];
        $crawler->filter('a.lsri')->each(function (Crawler $node) use (&$results, $abs) {
            $link = $node->attr('href');
            $formNode = $node->filter('li');
            $imgNode = $node->filter('img');
            $spanNode = $node->filter('span')->first();
            $strengthNode = $node->filter('.sr-strength');
            $results[] = [
                'link' => $abs($link),
                'form' => $formNode->count() ? $formNode->attr('title') : null,
                'name' => $spanNode->count() ? trim($spanNode->text()) : null,
                'strength' => $strengthNode->count() ? trim($strengthNode->text()) : null,
                'img' => $abs($imgNode->count() ? $imgNode->attr('src') : null),
            ];
        });

        return response()->json($results, 200, [], JSON_PRETTY_PRINT);
    }

    public function medexBrands(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $letter = strtolower((string) $request->query('letter', ''));
        $query = http_build_query(array_filter([
            'page' => $page,
            'alpha' => preg_match('/^[a-z]$/', $letter) ? $letter : null,
        ]));
        $html = $this->fetchMedex('https://medex.com.bd/brands'.($query ? '?'.$query : ''));
        if ($html === null) {
            return response()->json(['error' => 'Failed to fetch brands from MedEx'], 502);
        }

        $crawler = new Crawler($html);
        $rows = $crawler->filter('a.brand-card')->each(function (Crawler $card) {
            $name = $card->filter('.brand-card__name');
            $strength = $card->filter('.brand-card__strength');
            $generic = $card->filter('.brand-card__generic');
            $company = $card->filter('.brand-card__company');
            $icon = $card->filter('.dosage-icon');

            return [
                'name' => $name->count() ? trim($name->text()) : null,
                'strength' => $strength->count() ? trim($strength->text()) : null,
                'generic' => $generic->count() ? trim($generic->text()) : null,
                'manufacturer' => $company->count() ? trim($company->text()) : null,
                'form' => $icon->count() ? ($icon->attr('title') ?: trim($icon->attr('alt') ?? '')) : null,
                'link' => $card->attr('href'),
            ];
        });

        return response()->json(['page' => $page, 'rows' => array_values(array_filter($rows, fn ($row) => ! empty($row['name'])))]);
    }

    public function importMedexBrands(Request $request)
    {
        $request->validate([
            'rows' => 'required|array|min:1|max:200',
            'rows.*.name' => 'required|string|max:255',
        ]);

        $created = 0;
        foreach ($request->input('rows') as $row) {
            $brand = Brand::firstOrCreate(['name' => $row['name']], ['is_active' => true]);
            if ($brand->wasRecentlyCreated) {
                $created++;
            }
        }

        return response()->json(['created' => $created, 'received' => count($request->input('rows'))]);
    }

    public function medexCompanies(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $letter = strtolower((string) $request->query('letter', ''));
        $query = http_build_query(array_filter([
            'page' => $page,
            'alpha' => preg_match('/^[a-z]$/', $letter) ? $letter : null,
        ]));
        $html = $this->fetchMedex('https://medex.com.bd/companies'.($query ? '?'.$query : ''));
        if ($html === null) {
            return response()->json(['error' => 'Failed to fetch companies from MedEx'], 502);
        }

        $crawler = new Crawler($html);
        $rows = $crawler->filter('.data-row')->each(function (Crawler $row) {
            $link = $row->filter('.data-row-top a');
            if (! $link->count()) {
                return null;
            }
            $stats = trim(preg_replace('/\s+/', ' ', $row->filter('.col-xs-12')->last()->text()) ?? '');

            return [
                'name' => trim($link->text()),
                'link' => $link->attr('href'),
                'details' => $stats,
            ];
        });

        return response()->json(['page' => $page, 'rows' => array_values(array_filter($rows))]);
    }

    public function importMedexCompanies(Request $request)
    {
        $request->validate([
            'rows' => 'required|array|min:1|max:100',
            'rows.*.name' => 'required|string|max:255',
            'rows.*.details' => 'nullable|string|max:255',
        ]);

        $created = 0;
        foreach ($request->input('rows') as $row) {
            $manufacturer = Manufacturer::firstOrCreate(['name' => $row['name']], [
                'status' => true,
                'details' => $row['details'] ?? null,
            ]);
            if ($manufacturer->wasRecentlyCreated) {
                $created++;
            }
        }

        return response()->json(['created' => $created, 'received' => count($request->input('rows'))]);
    }

    private function fetchMedex(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && is_string($html) && $html !== '' ? $html : null;
    }

    public function medexProduct(Request $request)
    {
        $url = $request->query('url');
        if (!$url) {
            return response()->json(['error' => 'Missing required parameter: url'], 422);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200 || !$html) {
            return response()->json(['error' => 'Failed to fetch product page'], 502);
        }

        $crawler = new Crawler($html);
        $safeText = function ($selector) use ($crawler) {
            $nodes = $crawler->filter($selector);
            return $nodes->count() ? trim($nodes->first()->text()) : null;
        };
        $safeHtml = function ($selector) use ($crawler) {
            $nodes = $crawler->filter($selector);
            return $nodes->count() ? $nodes->first()->html() : null;
        };
        $abs = function ($maybe) {
            if (!$maybe) return null;
            if (str_starts_with($maybe, 'http')) return $maybe;
            return 'https://medex.com.bd' . $maybe;
        };

        $product = [];
        $product['name'] = $safeText('h1.page-heading-1-l.brand');
        $product['form'] = $safeText('h1.page-heading-1-l.brand small.h1-subtitle');
        $product['generic'] = $safeText('div[title="Generic Name"] a');
        $product['strength'] = $safeText('div[title="Strength"]');
        $product['manufacturer'] = $safeText('div[title="Manufactured by"] a');

        $prices = [];
        $crawler->filter('div.packages-wrapper span, div.packages-wrapper div span')->each(function (Crawler $n) use (&$prices) {
            $txt = trim($n->text() ?? '');
            if ($txt !== '' && preg_match('/[0-9]+(?:[\.,][0-9]{1,2})?/', $txt)) {
                $prices[] = $txt;
            }
        });
        $product['unit_price'] = $prices[0] ?? null;
        $product['strip_price'] = $prices[1] ?? null;
        $toNumeric = function ($s) {
            if (!$s) return null;
            $m = preg_replace('/[^0-9.,]/', '', $s);
            $m = str_replace(',', '', $m);
            if ($m === '') return null;
            $v = (float) $m;
            return is_finite($v) ? $v : null;
        };
        $product['numeric_unit_price'] = $toNumeric($product['unit_price']);
        $product['numeric_strip_price'] = $toNumeric($product['strip_price']);

        $product['alternate_forms'] = $crawler->filter('div.margin-tb-10 a.cbtn.btn-sibling-brands')->each(function (Crawler $node) use ($abs) {
            return [
                'url' => $abs($node->attr('href')),
                'title' => trim($node->attr('title') ?? ''),
                'text' => trim($node->text() ?? ''),
            ];
        });
        $product['monographs'] = $crawler->filter('div.modal-body a.prsinf-child-btn')->each(function (Crawler $node) use ($abs) {
            return [
                'title' => trim($node->attr('title') ?? ''),
                'url' => $abs($node->attr('href')),
                'text' => trim($node->text() ?? ''),
            ];
        });

        $product['indications'] = $safeText('#indications + .ac-body');
        $product['pharmacology'] = $safeText('#mode_of_action + .ac-body');
        $product['dosage'] = $safeHtml('#dosage + .ac-body');
        $product['interaction'] = $safeText('#interaction + .ac-body');
        $product['contraindications'] = $safeText('#contraindications + .ac-body');
        $product['side_effects'] = $safeText('#side_effects + .ac-body');
        $product['pregnancy_lactation'] = $safeText('#pregnancy_cat + .ac-body');
        $product['precautions'] = $safeText('#precautions + .ac-body');
        $product['special_populations'] = $safeText('#pediatric_uses + .ac-body');
        $product['overdose'] = $safeText('#overdose_effects + .ac-body');
        $product['therapeutic_class'] = $safeText('#drug_classes + .ac-body');
        // Pack size may appear with different structures; attempt multiple selectors
        $packSize = $safeText('div.packages-wrapper span.pack-size-info');
        if (!$packSize) {
            $packSize = $safeText('.pack-size-info');
        }
        if (!$packSize) {
            // Sometimes appears near packages-wrapper spans
            $nodes = $crawler->filter('div.packages-wrapper span');
            if ($nodes->count()) {
                foreach ($nodes as $node) {
                    $text = trim($node->textContent ?? '');
                    if (stripos($text, 'pack') !== false || preg_match('/\(.*\)/', $text)) {
                        $packSize = $text;
                        break;
                    }
                }
            }
        }
        $product['pack_size'] = $packSize;
        $product['storage'] = $safeText('#storage_conditions + .ac-body');

        // Derive MedEx identifiers from URL
        $medexId = null;
        $medexName = null;
        try {
            $u = new \Illuminate\Support\Str(); // placeholder to avoid import
        } catch (\Throwable $e) {
        }
        try {
            $parsed = parse_url($url);
            $path = $parsed['path'] ?? '';
            $parts = array_values(array_filter(explode('/', $path)));
            $idx = array_search('brands', $parts);
            if ($idx !== false && isset($parts[$idx + 1])) {
                $medexId = $parts[$idx + 1];
                $medexName = $parts[$idx + 2] ?? null;
            }
        } catch (\Throwable $e) {
        }
        $product['medex_id'] = $medexId;
        $product['medex_name'] = $medexName;

        // Existence check in DB: single query using ORs and subquery join to manufacturers
        $product['exists'] = \App\Models\Medicine::where(function ($q) use ($medexId, $medexName, $product) {
            if ($medexId) {
                $q->orWhere('medex_id', $medexId);
            }
            if ($medexName) {
                $q->orWhere('medex_name', $medexName);
            }
            if (!empty($product['name']) && !empty($product['manufacturer'])) {
                $q->orWhere(function ($q2) use ($product) {
                    $q2->where('name', $product['name'])
                        ->whereExists(function ($sub) use ($product) {
                            $sub->selectRaw('1')
                                ->from('manufacturers')
                                ->whereColumn('manufacturers.id', 'medicines.manufacturer_id')
                                ->where('manufacturers.name', $product['manufacturer']);
                        });
                });
            }
        })->exists();

        return response()->json($product, 200, [], JSON_PRETTY_PRINT);
    }

    public function storeExternal(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'medex_id' => 'nullable|string|max:255',
            'medex_name' => 'nullable|string|max:255',
            'details' => 'nullable|array',
        ]);

        $manufacturer = Manufacturer::firstOrCreate(['name' => $request->manufacturer], [
            'status' => true,
        ]);

        $category = null;
        if ($request->filled('category')) {
            $category = Category::firstOrCreate(['name' => $request->category], [
                'status' => true,
            ]);
        }

        $generic = null;
        if ($request->filled('generic_name')) {
            $generic = Generic::firstOrCreate(['name' => $request->generic_name], [
                'is_active' => true,
            ]);
        }

        $brandName = trim((string) $request->name);
        if ($request->filled('strength')) {
            $brandName = trim(str_ireplace($request->strength, '', $brandName));
        }
        $brandName = trim(preg_replace('/\s+/', ' ', $brandName) ?? '');
        if ($brandName === '') {
            $brandName = $request->name;
        }
        $brand = Brand::firstOrCreate(['name' => $brandName], [
            'is_active' => true,
        ]);

        $existing = Medicine::where('name', $request->name)
            ->where('manufacturer_id', $manufacturer->id)
            ->first();
        if ($existing) {
            $existing->fill([
                'generic_id' => $existing->generic_id ?: $generic?->id,
                'brand_id' => $existing->brand_id ?: $brand->id,
                'category_id' => $existing->category_id ?: $category?->id,
                'dosage_form' => $existing->dosage_form ?: $request->dosage_form,
                'generic_name' => $existing->generic_name ?: $request->generic_name,
            ]);
            $existing->save();

            return response()->json(['status' => 'duplicate', 'id' => $existing->id], 200);
        }

        do {
            $productId = Str::random(8);
        } while (Medicine::where('product_id', $productId)->exists());

        $medicine = Medicine::create([
            'product_id' => $productId,
            'name' => $request->name,
            'category_id' => $category?->id,
            'manufacturer_id' => $manufacturer->id,
            'generic_id' => $generic?->id,
            'brand_id' => $brand->id,
            'generic_name' => $request->generic_name,
            'strength' => $request->strength,
            'dosage_form' => $request->dosage_form,
            'price' => $request->price ?? 0,
            'manufacturer_price' => 0,
            'box_size' => 1,
            'status' => true,
            'details' => $request->filled('details') ? json_encode($request->input('details')) : null,
            'medex_id' => $request->medex_id,
            'medex_name' => $request->medex_name,
        ]);

        $this->generateDefaultCodes($medicine);

        return response()->json(['status' => 'created', 'id' => $medicine->id]);
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
                'name' => $unit['name'],
                'factor_to_base' => max(1, (int) ($unit['factor_to_base'] ?? 1)),
                'sort' => $index,
            ]);
        }
    }
}
