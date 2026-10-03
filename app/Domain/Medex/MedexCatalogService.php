<?php

namespace App\Domain\Medex;

use App\Domain\Medex\Parsers\BrandListParser;
use App\Domain\Medex\Parsers\CompanyParser;
use App\Domain\Medex\Parsers\DosageFormParser;
use App\Domain\Medex\Parsers\GenericParser;
use App\Domain\Medex\Parsers\ProductParser;
use App\Domain\Medex\Parsers\SearchParser;
use App\Models\Brand;
use App\Models\DosageForm;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\MedexBrandIndex;
use App\Models\Medicine;
use App\Models\MedicineType;
use Illuminate\Support\Str;

class MedexCatalogService
{
    public function __construct(
        private readonly MedexClient $client,
        private readonly SearchParser $searchParser,
        private readonly BrandListParser $brandListParser,
        private readonly CompanyParser $companyParser,
        private readonly GenericParser $genericParser,
        private readonly DosageFormParser $dosageFormParser,
        private readonly ProductParser $productParser,
    ) {}

    public function search(string $q): array
    {
        $html = $this->client->get('/ajax/search', [
            'searchtype' => 'search',
            'searchkey'  => $q,
        ], false);

        return $html ? $this->searchParser->parse($html) : [];
    }

    public function product(string $url): ?array
    {
        $html = $this->client->get($url, [], false);
        if (! $html) {
            return null;
        }
        $product = $this->productParser->parse($html, $url);
        $product['exists'] = $this->medicineExists($product);

        return $product;
    }

    public function brands(int $page, string $letter, string $segment): array
    {
        $query = array_filter([
            'page'   => $page,
            'alpha'  => preg_match('/^[a-z]$/', $letter) ? $letter : null,
            'herbal' => $segment === 'herbal' ? 1 : null,
        ]);
        $html = $this->client->get('/brands', $query);
        if (! $html) {
            return ['page' => $page, 'segment' => $segment, 'rows' => [], 'error' => 'Failed to fetch brands from MedEx'];
        }

        return [
            'page'    => $page,
            'segment' => $segment,
            'rows'    => $this->brandListParser->parse($html),
        ];
    }

    public function companies(int $page, string $letter, string $segment): array
    {
        $query = array_filter([
            'page'   => $page,
            'alpha'  => preg_match('/^[a-z]$/', $letter) ? $letter : null,
            'herbal' => $segment === 'herbal' ? 1 : null,
        ]);
        $html = $this->client->get('/companies', $query);
        if (! $html) {
            return ['page' => $page, 'segment' => $segment, 'rows' => [], 'error' => 'Failed to fetch companies from MedEx'];
        }

        return [
            'page'    => $page,
            'segment' => $segment,
            'rows'    => $this->companyParser->parse($html),
        ];
    }

    public function generics(int $page, string $letter, string $segment): array
    {
        $query = array_filter([
            'page'   => $page,
            'alpha'  => preg_match('/^[a-z]$/', $letter) ? $letter : null,
            'herbal' => $segment === 'herbal' ? 1 : null,
        ]);
        $html = $this->client->get('/generics', $query);
        if (! $html) {
            return ['page' => $page, 'segment' => $segment, 'rows' => [], 'error' => 'Failed to fetch generics from MedEx'];
        }

        return [
            'page'    => $page,
            'segment' => $segment,
            'rows'    => $this->genericParser->parse($html),
        ];
    }

    public function dosageForms(): array
    {
        $html = $this->client->get('/dosage-forms');
        if (! $html) {
            return ['rows' => [], 'error' => 'Failed to fetch dosage forms from MedEx'];
        }

        return ['rows' => $this->dosageFormParser->parse($html)];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{created: int, updated: int, received: int}
     */
    public function importDosageForms(array $rows): array
    {
        $created = $updated = 0;
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $form = DosageForm::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            $payload = [
                'name'        => $name,
                'medex_slug'  => $row['medex_slug'] ?? $form?->medex_slug,
                'brand_count' => (int) ($row['brand_count'] ?? 0),
                'is_active'   => true,
            ];
            if ($form) {
                $form->fill($payload)->save();
                $updated++;
            } else {
                DosageForm::create($payload);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated, 'received' => count($rows)];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{created: int, updated: int, received: int}
     */
    public function importCompanies(array $rows, string $segment = 'allopathic'): array
    {
        $created = $updated = 0;
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $model = Manufacturer::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            $payload = [
                'name'       => $name,
                'status'     => true,
                'details'    => $row['details'] ?? $model?->details,
                'segment'    => $segment,
                'medex_path' => $row['medex_path'] ?? $row['link'] ?? $model?->medex_path,
                'medex_id'   => $row['medex_id'] ?? $model?->medex_id,
                'meta'       => array_filter(['stats' => $row['details'] ?? null]),
            ];
            if ($model) {
                $model->fill($payload)->save();
                $updated++;
            } else {
                Manufacturer::create($payload);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated, 'received' => count($rows)];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{created: int, updated: int, received: int}
     */
    public function importGenerics(array $rows, string $segment = 'allopathic'): array
    {
        $created = $updated = 0;
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $model = Generic::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            $payload = [
                'name'       => $name,
                'is_active'  => true,
                'segment'    => $segment,
                'medex_path' => $row['medex_path'] ?? $row['link'] ?? $model?->medex_path,
                'medex_id'   => $row['medex_id'] ?? $model?->medex_id,
                'meta'       => ['brand_count' => (int) ($row['brand_count'] ?? 0)],
            ];
            if ($model) {
                $model->fill($payload)->save();
                $updated++;
            } else {
                Generic::create($payload);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated, 'received' => count($rows)];
    }

    /**
     * Index MedEx brand cards locally; upsert Brand family names only.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return array{indexed: int, brands: int, received: int}
     */
    public function importBrandIndex(array $rows, string $segment = 'allopathic'): array
    {
        $indexed = $brands = 0;
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $family = $this->brandFamilyName($name, (string) ($row['strength'] ?? ''), (string) ($row['form'] ?? ''));
            $brand  = Brand::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($family)])->first();
            if (! $brand) {
                $brand = Brand::create([
                    'name'      => $family,
                    'is_active' => true,
                    'segment'   => $segment,
                ]);
                $brands++;
            } else {
                $brand->fill(['segment' => $segment ?: $brand->segment])->save();
            }

            $generic = null;
            if (! empty($row['generic'])) {
                $generic = Generic::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($row['generic'])])->first()
                    ?: Generic::create([
                        'name'      => $row['generic'],
                        'is_active' => true,
                        'segment'   => $segment,
                    ]);
            }

            $manufacturer = null;
            if (! empty($row['manufacturer'])) {
                $manufacturer = Manufacturer::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($row['manufacturer'])])->first()
                    ?: Manufacturer::create([
                        'name'    => $row['manufacturer'],
                        'status'  => true,
                        'segment' => $segment,
                    ]);
            }

            $form = null;
            if (! empty($row['form'])) {
                $form = DosageForm::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($row['form'])])->first()
                    ?: DosageForm::create([
                        'name'      => $row['form'],
                        'is_active' => true,
                    ]);
            }

            $medexId = $row['medex_id'] ?? null;
            $lookup  = MedexBrandIndex::query()
                ->when($medexId, fn ($q) => $q->where('medex_id', $medexId)->where('segment', $segment))
                ->when(! $medexId, fn ($q) => $q->where('name', $name)->where('segment', $segment)->where('strength', $row['strength'] ?? null))
                ->first();

            $payload = [
                'name'              => $name,
                'strength'          => $row['strength'] ?? null,
                'form'              => $row['form'] ?? null,
                'generic_name'      => $row['generic'] ?? null,
                'manufacturer_name' => $row['manufacturer'] ?? null,
                'segment'           => $segment,
                'medex_path'        => $row['medex_path'] ?? $row['link'] ?? null,
                'medex_id'          => $medexId,
                'medex_slug'        => $row['medex_slug'] ?? null,
                'brand_id'          => $brand->id,
                'generic_id'        => $generic?->id,
                'manufacturer_id'   => $manufacturer?->id,
                'dosage_form_id'    => $form?->id,
            ];

            if ($lookup) {
                $lookup->fill($payload)->save();
            } else {
                MedexBrandIndex::create($payload);
            }
            $indexed++;
        }

        return ['indexed' => $indexed, 'brands' => $brands, 'received' => count($rows)];
    }

    public function resolveSegmentType(string $segment): MedicineType
    {
        $name = match ($segment) {
            'herbal' => 'Herbal',
            'device' => 'Device',
            default  => 'Allopathic',
        };

        return MedicineType::firstOrCreate(['name' => $name]);
    }

    public function resolveDosageForm(?string $name): ?DosageForm
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        return DosageForm::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?: DosageForm::create(['name' => $name, 'is_active' => true]);
    }

    public function brandFamilyName(string $medicineName, string $strength = '', string $form = ''): string
    {
        $brandName = $medicineName;
        if ($strength !== '') {
            $brandName = trim(str_ireplace($strength, '', $brandName));
        }
        if ($form !== '') {
            $brandName = trim((string) preg_replace('/\b'.preg_quote($form, '/').'\b/i', '', $brandName));
        }
        foreach (['Tablet', 'Capsule', 'Syrup', 'Injection', 'Suspension', 'Cream', 'Ointment', 'Drop', 'Drops'] as $formWord) {
            $brandName = trim((string) preg_replace('/\b'.preg_quote($formWord, '/').'\b/i', '', $brandName));
        }
        $brandName = trim((string) preg_replace('/\s+/', ' ', $brandName));

        return $brandName !== '' ? $brandName : $medicineName;
    }

    public function firstOrCreateByName(string $model, string $name, array $attributes = [])
    {
        $name = trim($name);
        $existing = $model::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
        if ($existing) {
            return $existing;
        }

        return $model::create(array_merge(['name' => $name], $attributes));
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function medicineExists(array $product): bool
    {
        if (! empty($product['medex_id'])) {
            return Medicine::query()->where('medex_id', $product['medex_id'])->exists();
        }

        if (! empty($product['name']) && ! empty($product['manufacturer'])) {
            return Medicine::query()
                ->where('name', $product['name'])
                ->whereHas('manufacturer', fn ($m) => $m->where('name', $product['manufacturer']))
                ->exists();
        }

        return false;
    }
}
