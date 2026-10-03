<?php

namespace App\Domain\Medex\Parsers;

use Symfony\Component\DomCrawler\Crawler;

class DosageFormParser
{
    /**
     * @return list<array{name: string, brand_count: int, medex_slug: ?string, link: ?string}>
     */
    public function parse(string $html): array
    {
        $crawler = new Crawler($html);
        $rows    = [];

        $crawler->filter('a')->each(function (Crawler $node) use (&$rows) {
            $href = (string) ($node->attr('href') ?? '');
            if (! str_contains($href, '/dosage-forms/') && ! str_contains($href, 'dosages')) {
                // MedEx cards are often linked blocks; fall through to text heuristics
            }

            $text = trim(preg_replace('/\s+/', ' ', $node->text()) ?? '');
            if ($text === '' || strlen($text) < 3) {
                return;
            }

            if (! preg_match('/^(.+?)\s+(\d+)\s+brand names?$/i', $text, $m)
                && ! preg_match('/^(.+?)\s+(\d+)\s+brand name$/i', $text, $m)) {
                return;
            }

            $name = trim($m[1]);
            if ($name === '' || str_contains(strtolower($name), 'list of')) {
                return;
            }

            $slug = null;
            if (preg_match('~/dosage-forms/([^/?]+)~', $href, $sm)) {
                $slug = $sm[1];
            }

            $rows[$name] = [
                'name'        => $name,
                'brand_count' => (int) $m[2],
                'medex_slug'  => $slug,
                'link'        => $href ?: null,
            ];
        });

        // Fallback: page body lines
        if ($rows === []) {
            $body = trim(preg_replace('/\s+/', ' ', $crawler->filter('body')->text()) ?? '');
            if (preg_match_all('/([A-Za-z][A-Za-z0-9 \/\-\(\)]+?)\s+(\d+)\s+brand names?/i', $body, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $name = trim($m[1]);
                    if (str_contains(strtolower($name), 'list of')) {
                        continue;
                    }
                    $rows[$name] = [
                        'name'        => $name,
                        'brand_count' => (int) $m[2],
                        'medex_slug'  => null,
                        'link'        => null,
                    ];
                }
            }
        }

        return array_values($rows);
    }
}
