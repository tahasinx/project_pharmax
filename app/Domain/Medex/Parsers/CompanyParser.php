<?php

namespace App\Domain\Medex\Parsers;

use App\Domain\Medex\MedexClient;
use Symfony\Component\DomCrawler\Crawler;

class CompanyParser
{
    public function __construct(private readonly MedexClient $client) {}

    /**
     * @return list<array{name: string, link: ?string, details: ?string, medex_path: ?string, medex_id: ?string}>
     */
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);

        $rows = $crawler->filter('.data-row')->each(function (Crawler $row) {
            $link = $row->filter('.data-row-top a');
            if (! $link->count()) {
                return null;
            }
            $href = $link->attr('href');
            $stats = trim(preg_replace('/\s+/', ' ', $row->filter('.col-xs-12')->last()->text()) ?? '');
            [$medexId, $path] = $this->extractId($href);

            return [
                'name'       => trim($link->text()),
                'link'       => $this->client->absolute($href),
                'details'    => $stats !== '' ? $stats : null,
                'medex_path' => $path,
                'medex_id'   => $medexId,
            ];
        });

        return array_values(array_filter($rows));
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function extractId(?string $href): array
    {
        if (! $href) {
            return [null, null];
        }
        $path = parse_url($href, PHP_URL_PATH) ?: $href;
        $parts = array_values(array_filter(explode('/', $path)));
        $idx = array_search('companies', $parts, true);
        if ($idx === false) {
            return [null, $path];
        }

        return [$parts[$idx + 1] ?? null, $path];
    }
}
