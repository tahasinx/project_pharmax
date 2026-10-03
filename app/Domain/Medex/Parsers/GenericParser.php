<?php

namespace App\Domain\Medex\Parsers;

use App\Domain\Medex\MedexClient;
use Symfony\Component\DomCrawler\Crawler;

class GenericParser
{
    public function __construct(private readonly MedexClient $client) {}

    /**
     * @return list<array{name: string, brand_count: int, link: ?string, medex_path: ?string, medex_id: ?string}>
     */
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $rows    = [];

        $crawler->filter('a')->each(function (Crawler $node) use (&$rows) {
            $href = (string) ($node->attr('href') ?? '');
            if (! str_contains($href, '/generics/')) {
                return;
            }
            $text = trim(preg_replace('/\s+/', ' ', $node->text()) ?? '');
            if ($text === '') {
                return;
            }

            $brandCount = 0;
            $name       = $text;
            if (preg_match('/^(.+?)\s+(\d+)\s+available brands?$/i', $text, $m)) {
                $name       = trim($m[1]);
                $brandCount = (int) $m[2];
            }

            // Parent containers often include the brand-count text separately
            $parentText = trim(preg_replace('/\s+/', ' ', $node->ancestors()->first()?->text() ?? '') ?? '');
            if ($brandCount === 0 && preg_match('/(\d+)\s+available brands?/i', $parentText, $pm)) {
                $brandCount = (int) $pm[1];
            }

            [$medexId, $path] = $this->extractId($href);
            $key = $medexId ?: $name;
            $rows[$key] = [
                'name'        => $name,
                'brand_count' => $brandCount,
                'link'        => $this->client->absolute($href),
                'medex_path'  => $path,
                'medex_id'    => $medexId,
            ];
        });

        return array_values($rows);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function extractId(string $href): array
    {
        $path = parse_url($href, PHP_URL_PATH) ?: $href;
        $parts = array_values(array_filter(explode('/', $path)));
        $idx = array_search('generics', $parts, true);
        if ($idx === false) {
            return [null, $path];
        }

        return [$parts[$idx + 1] ?? null, $path];
    }
}
