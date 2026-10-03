<?php

namespace App\Jobs;

use App\Domain\Medex\MedexCatalogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportMedexBrandPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $page,
        public string $letter = '',
        public string $segment = 'allopathic',
    ) {}

    public function handle(MedexCatalogService $catalog): void
    {
        $result = $catalog->brands($this->page, $this->letter, $this->segment);
        if (! empty($result['rows'])) {
            $catalog->importBrandIndex($result['rows'], $this->segment);
        }
    }
}
