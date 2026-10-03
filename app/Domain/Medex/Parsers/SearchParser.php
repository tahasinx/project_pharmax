<?php

namespace App\Domain\Medex\Parsers;

use App\Domain\Medex\MedexClient;
use Symfony\Component\DomCrawler\Crawler;

class SearchParser
{
    public function __construct(private readonly MedexClient $client) {}

    /**
     * @return list<array{link: ?string, form: ?string, name: ?string, strength: ?string, img: ?string, medex_id: ?string, medex_slug: ?string, medex_path: ?string}>
     */
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $results = [];
        $crawler->filter('a.lsri')->each(function (Crawler $node) use (&$results) {
            $formNode     = $node->filter('li');
            $imgNode      = $node->filter('img');
            $spanNode     = $node->filter('span')->first();
            $strengthNode = $node->filter('.sr-strength');
            $href         = $node->attr('href');
            [$medexId, $slug, $path] = $this->extractId($href);
            $results[]    = [
                'link'       => $this->client->absolute($href),
                'form'       => $formNode->count() ? $formNode->attr('title') : null,
                'name'       => $spanNode->count() ? trim($spanNode->text()) : null,
                'strength'   => $strengthNode->count() ? trim($strengthNode->text()) : null,
                'img'        => $this->client->absolute($imgNode->count() ? $imgNode->attr('src') : null),
                'medex_id'   => $medexId,
                'medex_slug' => $slug,
                'medex_path' => $path,
            ];
        });

        return $results;
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
        $parts = array_values(array_filter(explode('/', (string) $path)));
        $idx = array_search('brands', $parts, true);
        if ($idx === false) {
            return [null, null, $path];
        }

        return [$parts[$idx + 1] ?? null, $parts[$idx + 2] ?? null, $path];
    }
}
