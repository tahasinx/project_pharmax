<?php

namespace App\Domain\Medex\Parsers;

use App\Domain\Medex\MedexClient;
use Symfony\Component\DomCrawler\Crawler;

class BrandListParser
{
    public function __construct(private readonly MedexClient $client) {}

    /**
     * @return list<array{name: ?string, strength: ?string, generic: ?string, manufacturer: ?string, form: ?string, link: ?string, medex_path: ?string, medex_id: ?string, medex_slug: ?string}>
     */
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $rows    = $crawler->filter('a.brand-card')->each(function (Crawler $card) {
            $name     = $card->filter('.brand-card__name');
            $strength = $card->filter('.brand-card__strength');
            $generic  = $card->filter('.brand-card__generic');
            $company  = $card->filter('.brand-card__company');
            $icon     = $card->filter('.dosage-icon');
            $href     = $card->attr('href');
            [$medexId, $slug, $path] = $this->extractId($href);

            return [
                'name'         => $name->count() ? trim($name->text()) : null,
                'strength'     => $strength->count() ? trim($strength->text()) : null,
                'generic'      => $generic->count() ? trim($generic->text()) : null,
                'manufacturer' => $company->count() ? trim($company->text()) : null,
                'form'         => $icon->count() ? ($icon->attr('title') ?: trim($icon->attr('alt') ?? '')) : null,
                'link'         => $this->client->absolute($href),
                'medex_path'   => $path,
                'medex_id'     => $medexId,
                'medex_slug'   => $slug,
            ];
        });

        return array_values(array_filter($rows, fn ($row) => ! empty($row['name'])));
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    private function extractId(?string $href): array
    {
        if (! $href) {
            return [null, null, null];
        }
        $path = parse_url($href, PHP_URL_PATH) ?: $href;
        $parts = array_values(array_filter(explode('/', $path)));
        $idx = array_search('brands', $parts, true);
        if ($idx === false) {
            return [null, null, $path];
        }

        return [$parts[$idx + 1] ?? null, $parts[$idx + 2] ?? null, $path];
    }
}
