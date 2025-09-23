<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\DomCrawler\Crawler;
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

    // MedEx proxy: product details
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
            'price' => 'nullable|numeric|min:0',
            'medex_id' => 'nullable|string|max:255',
            'medex_name' => 'nullable|string|max:255',
        ]);

        // Resolve or create category/manufacturer
        $manufacturer = Manufacturer::firstOrCreate(['name' => $request->manufacturer], [
            'status' => true,
        ]);

        $category = null;
        if ($request->filled('category')) {
            $category = Category::firstOrCreate(['name' => $request->category], [
                'status' => true,
            ]);
        }

        // Duplicate check by name + manufacturer
        $existing = Medicine::where('name', $request->name)
            ->where('manufacturer_id', $manufacturer->id)
            ->first();
        if ($existing) {
            return response()->json(['status' => 'duplicate', 'id' => $existing->id], 200);
        }

        // Create medicine
        do {
            $productId = Str::random(8);
        } while (Medicine::where('product_id', $productId)->exists());

        $medicine = Medicine::create([
            'product_id' => $productId,
            'name' => $request->name,
            'category_id' => $category?->id,
            'manufacturer_id' => $manufacturer->id,
            'generic_name' => $request->generic_name,
            'strength' => $request->strength,
            'price' => $request->price ?? 0,
            'manufacturer_price' => 0,
            'box_size' => 1,
            'status' => true,
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
            'barcode_data' => $barcodeData,
            'barcode_type' => 'code128',
        ]);
    }
}
