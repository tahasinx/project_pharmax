<?php

namespace App\Http\Controllers;

use App\Domain\Medex\MedexCatalogService;
use App\Domain\Medex\MedexClient;
use App\Jobs\SyncMedexDirectoryJob;
use App\Models\DosageForm;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\MedexBrandIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MedexController extends Controller
{
    public function __construct(private readonly MedexCatalogService $catalog) {}

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        return response()->json($this->catalog->search($q));
    }

    public function product(Request $request)
    {
        $url = (string) $request->query('url', '');
        if ($url === '') {
            return response()->json(['error' => 'Missing required parameter: url'], 422);
        }
        if (! app(MedexClient::class)->isAllowedUrl($url)) {
            return response()->json(['error' => 'URL host is not allowed'], 422);
        }
        $product = $this->catalog->product($url);
        if (! $product) {
            return response()->json(['error' => 'Failed to fetch product page'], 502);
        }

        return response()->json($product);
    }

    public function brands(Request $request)
    {
        $segment = app(MedexClient::class)->segmentFromRequest($request->query('herbal'));
        $result  = $this->catalog->brands(
            max(1, (int) $request->query('page', 1)),
            strtolower((string) $request->query('letter', '')),
            $segment,
        );
        if (isset($result['error'])) {
            return response()->json($result, 502);
        }

        return response()->json($result);
    }

    public function importBrands(Request $request)
    {
        $data = $request->validate([
            'rows'                  => 'required|array|min:1|max:200',
            'rows.*.name'           => 'required|string|max:255',
            'rows.*.strength'       => 'nullable|string|max:255',
            'rows.*.generic'        => 'nullable|string|max:255',
            'rows.*.manufacturer'   => 'nullable|string|max:255',
            'rows.*.form'           => 'nullable|string|max:255',
            'rows.*.link'           => 'nullable|string|max:500',
            'rows.*.medex_path'     => 'nullable|string|max:500',
            'rows.*.medex_id'       => 'nullable|string|max:64',
            'rows.*.medex_slug'     => 'nullable|string|max:255',
            'herbal'                => 'nullable|boolean',
            'segment'               => 'nullable|in:allopathic,herbal',
        ]);
        $segment = $data['segment'] ?? app(MedexClient::class)->segmentFromRequest($data['herbal'] ?? false);

        return response()->json($this->catalog->importBrandIndex($data['rows'], $segment));
    }

    public function companies(Request $request)
    {
        $segment = app(MedexClient::class)->segmentFromRequest($request->query('herbal'));
        $result  = $this->catalog->companies(
            max(1, (int) $request->query('page', 1)),
            strtolower((string) $request->query('letter', '')),
            $segment,
        );
        if (isset($result['error'])) {
            return response()->json($result, 502);
        }

        return response()->json($result);
    }

    public function importCompanies(Request $request)
    {
        $data = $request->validate([
            'rows'             => 'required|array|min:1|max:100',
            'rows.*.name'      => 'required|string|max:255',
            'rows.*.details'   => 'nullable|string|max:255',
            'rows.*.link'      => 'nullable|string|max:500',
            'rows.*.medex_path'=> 'nullable|string|max:500',
            'rows.*.medex_id'  => 'nullable|string|max:64',
            'herbal'           => 'nullable|boolean',
            'segment'          => 'nullable|in:allopathic,herbal',
        ]);
        $segment = $data['segment'] ?? app(MedexClient::class)->segmentFromRequest($data['herbal'] ?? false);

        return response()->json($this->catalog->importCompanies($data['rows'], $segment));
    }

    public function generics(Request $request)
    {
        $segment = app(MedexClient::class)->segmentFromRequest($request->query('herbal'));
        $result  = $this->catalog->generics(
            max(1, (int) $request->query('page', 1)),
            strtolower((string) $request->query('letter', '')),
            $segment,
        );
        if (isset($result['error'])) {
            return response()->json($result, 502);
        }

        return response()->json($result);
    }

    public function importGenerics(Request $request)
    {
        $data = $request->validate([
            'rows'               => 'required|array|min:1|max:200',
            'rows.*.name'        => 'required|string|max:255',
            'rows.*.brand_count' => 'nullable|integer|min:0',
            'rows.*.link'        => 'nullable|string|max:500',
            'rows.*.medex_path'  => 'nullable|string|max:500',
            'rows.*.medex_id'    => 'nullable|string|max:64',
            'herbal'             => 'nullable|boolean',
            'segment'            => 'nullable|in:allopathic,herbal',
        ]);
        $segment = $data['segment'] ?? app(MedexClient::class)->segmentFromRequest($data['herbal'] ?? false);

        return response()->json($this->catalog->importGenerics($data['rows'], $segment));
    }

    public function dosageForms()
    {
        $result = $this->catalog->dosageForms();
        if (isset($result['error'])) {
            return response()->json($result, 502);
        }

        return response()->json($result);
    }

    public function importDosageForms(Request $request)
    {
        $data = $request->validate([
            'rows'               => 'required|array|min:1|max:300',
            'rows.*.name'        => 'required|string|max:255',
            'rows.*.brand_count' => 'nullable|integer|min:0',
            'rows.*.medex_slug'  => 'nullable|string|max:255',
        ]);

        return response()->json($this->catalog->importDosageForms($data['rows']));
    }

    public function localIndex(Request $request)
    {
        $segment = (string) $request->query('segment', '');
        $q       = trim((string) $request->query('q', ''));

        return response()->json([
            'dosage_forms'  => DosageForm::query()->where('is_active', true)->orderBy('name')->limit(300)->get(['id', 'dosage_form_id', 'name', 'brand_count']),
            'generics'      => Generic::query()
                ->when($segment !== '', fn ($q2) => $q2->where('segment', $segment))
                ->when($q !== '', fn ($q2) => $q2->where('name', 'like', "%{$q}%"))
                ->orderBy('name')->limit(100)->get(['id', 'generic_id', 'name', 'segment']),
            'manufacturers' => Manufacturer::query()
                ->where('status', true)
                ->when($segment !== '', fn ($q2) => $q2->where('segment', $segment))
                ->when($q !== '', fn ($q2) => $q2->where('name', 'like', "%{$q}%"))
                ->orderBy('name')->limit(100)->get(['id', 'manufacturer_id', 'name', 'segment']),
            'brand_index'   => MedexBrandIndex::query()
                ->when($segment !== '', fn ($q2) => $q2->where('segment', $segment))
                ->when($q !== '', function ($q2) use ($q) {
                    $q2->where(function ($w) use ($q) {
                        $w->where('name', 'like', "%{$q}%")
                            ->orWhere('generic_name', 'like', "%{$q}%")
                            ->orWhere('manufacturer_name', 'like', "%{$q}%");
                    });
                })
                ->latest('id')->limit(60)->get(),
            'stats' => [
                'dosage_forms'  => DosageForm::count(),
                'generics'      => Generic::count(),
                'manufacturers' => Manufacturer::count(),
                'brand_index'   => MedexBrandIndex::count(),
                'last_sync'     => Cache::get('medex:last_sync'),
            ],
        ]);
    }

    public function sync(Request $request, string $directory)
    {
        $directory = strtolower($directory);
        abort_unless(in_array($directory, ['dosage-forms', 'companies', 'generics', 'brands'], true), 404);

        $data = $request->validate([
            'herbal'  => 'nullable|boolean',
            'segment' => 'nullable|in:allopathic,herbal',
            'letters' => 'nullable|array',
            'letters.*' => 'string|size:1',
        ]);
        $segment = $data['segment'] ?? app(MedexClient::class)->segmentFromRequest($data['herbal'] ?? false);
        $letters = $data['letters'] ?? range('a', 'z');
        $tenantDatabase = (string) config('database.connections.mysql.database');

        SyncMedexDirectoryJob::dispatch($directory, $segment, $letters, $tenantDatabase ?: null);

        return response()->json([
            'queued'    => true,
            'directory' => $directory,
            'segment'   => $segment,
            'letters'   => $letters,
            'database'  => $tenantDatabase ?: null,
        ]);
    }

    public function status()
    {
        $client = app(MedexClient::class);

        return response()->json([
            'enabled'   => $client->enabled(),
            'last_sync' => Cache::get('medex:last_sync'),
            'cache_ttl' => $client->cacheTtl(),
            'counts'    => [
                'dosage_forms'  => DosageForm::count(),
                'generics'      => Generic::count(),
                'manufacturers' => Manufacturer::whereNotNull('medex_id')->orWhereNotNull('medex_path')->count(),
                'brand_index'   => MedexBrandIndex::count(),
            ],
        ]);
    }
}
