<?php

namespace App\Jobs;

use App\Domain\Medex\MedexCatalogService;
use App\Services\Platform\TenantRuntime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncMedexDirectoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  list<string>  $letters
     */
    public function __construct(
        public string $directory,
        public string $segment = 'allopathic',
        public array $letters = [],
        public ?string $tenantDatabase = null,
    ) {}

    public function handle(MedexCatalogService $catalog): void
    {
        $run = function () use ($catalog) {
            $this->sync($catalog);
        };

        if (filled($this->tenantDatabase)) {
            TenantRuntime::runOn((string) $this->tenantDatabase, $run);

            return;
        }

        $run();
    }

    private function sync(MedexCatalogService $catalog): void
    {
        $letters = $this->letters !== [] ? $this->letters : range('a', 'z');

        try {
            match ($this->directory) {
                'dosage-forms' => $this->syncDosageForms($catalog),
                'companies'    => $this->syncPaged($catalog, 'companies', $letters),
                'generics'     => $this->syncPaged($catalog, 'generics', $letters),
                'brands'       => $this->syncPaged($catalog, 'brands', $letters),
                default        => null,
            };
            Cache::put('medex:last_sync', [
                'directory' => $this->directory,
                'segment'   => $this->segment,
                'database'  => $this->tenantDatabase,
                'at'        => now()->toIso8601String(),
            ], now()->addDays(30));
        } catch (\Throwable $e) {
            Log::error('MedEx sync failed', [
                'directory' => $this->directory,
                'segment'   => $this->segment,
                'database'  => $this->tenantDatabase,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function syncDosageForms(MedexCatalogService $catalog): void
    {
        $result = $catalog->dosageForms();
        if (! empty($result['rows'])) {
            $catalog->importDosageForms($result['rows']);
        }
    }

    /**
     * @param  list<string>  $letters
     */
    private function syncPaged(MedexCatalogService $catalog, string $kind, array $letters): void
    {
        foreach ($letters as $letter) {
            for ($page = 1; $page <= 40; $page++) {
                $result = match ($kind) {
                    'companies' => $catalog->companies($page, $letter, $this->segment),
                    'generics'  => $catalog->generics($page, $letter, $this->segment),
                    default     => $catalog->brands($page, $letter, $this->segment),
                };
                $rows = $result['rows'] ?? [];
                if ($rows === []) {
                    break;
                }
                match ($kind) {
                    'companies' => $catalog->importCompanies($rows, $this->segment),
                    'generics'  => $catalog->importGenerics($rows, $this->segment),
                    default     => $catalog->importBrandIndex($rows, $this->segment),
                };
                if (count($rows) < 10) {
                    break;
                }
            }
        }
    }
}
