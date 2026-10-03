<?php

namespace App\Domain\Medex\Parsers;

use App\Domain\Medex\MedexClient;
use Symfony\Component\DomCrawler\Crawler;

class ProductParser
{
    public function __construct(private readonly MedexClient $client) {}

    /**
     * @return array<string, mixed>
     */
    public function parse(string $html, string $url): array
    {
        $crawler  = new Crawler($html);
        $safeText = function ($selector) use ($crawler) {
            $nodes = $crawler->filter($selector);

            return $nodes->count() ? trim($nodes->first()->text()) : null;
        };
        $safeHtml = function ($selector) use ($crawler) {
            $nodes = $crawler->filter($selector);

            return $nodes->count() ? $nodes->first()->html() : null;
        };

        $product                 = [];
        $product['name']         = $safeText('h1.page-heading-1-l.brand');
        $product['form']         = $safeText('h1.page-heading-1-l.brand small.h1-subtitle');
        $product['generic']      = $safeText('div[title="Generic Name"] a');
        $product['strength']     = $safeText('div[title="Strength"]');
        $product['manufacturer'] = $safeText('div[title="Manufactured by"] a');

        $prices = [];
        $crawler->filter('div.packages-wrapper span, div.packages-wrapper div span')->each(function (Crawler $n) use (&$prices) {
            $txt = trim($n->text() ?? '');
            if ($txt !== '') {
                $prices[] = $txt;
            }
        });
        $product['prices'] = $prices;

        $packSize = null;
        $crawler->filter('div.packages-wrapper')->each(function (Crawler $n) use (&$packSize) {
            $txt = trim(preg_replace('/\s+/', ' ', $n->text()) ?? '');
            if ($txt !== '' && $packSize === null) {
                $packSize = $txt;
            }
        });
        $product['pack_size'] = $packSize;
        $product['storage']   = $safeText('#storage_conditions + .ac-body');

        $this->hydratePrices($product, $prices, $packSize);

        foreach ([
            'indications'           => '#indications + .ac-body',
            'composition'           => '#composition + .ac-body',
            'pharmacology'          => '#mode_of_action + .ac-body',
            'dosage'                => '#dosage + .ac-body',
            'administration'        => '#administration + .ac-body',
            'interaction'           => '#interaction + .ac-body',
            'contraindications'     => '#contraindications + .ac-body',
            'side_effects'          => '#side_effects + .ac-body',
            'pregnancy'             => '#pregnancy_lactation + .ac-body',
            'precautions'           => '#precautions_warnings + .ac-body',
            'overdose'              => '#overdose_effects + .ac-body',
        ] as $key => $selector) {
            $htmlBody = $this->sanitizeHtml($safeHtml($selector));
            if ($htmlBody) {
                $product[$key] = $htmlBody;
                $product[$key.'_html'] = true;
            } else {
                $product[$key] = $safeText($selector);
                $product[$key.'_html'] = false;
            }
        }

        $img = $crawler->filter('.brand-img img')->first();
        if (! $img->count()) {
            $img = $crawler->filter('.companylogo img')->first();
        }
        $product['img'] = $img->count() ? $this->client->absolute($img->attr('src')) : null;

        $medexId = $medexName = null;
        $parsed  = parse_url($url);
        $path    = $parsed['path'] ?? '';
        $parts   = array_values(array_filter(explode('/', $path)));
        $idx     = array_search('brands', $parts, true);
        if ($idx !== false && isset($parts[$idx + 1])) {
            $medexId   = $parts[$idx + 1];
            $medexName = $parts[$idx + 2] ?? null;
        }
        $product['medex_id']   = $medexId;
        $product['medex_name'] = $medexName;
        $product['medex_path'] = $path ?: null;
        $product['url']        = $url;

        return $product;
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  list<string>  $prices
     */
    private function hydratePrices(array &$product, array $prices, ?string $packSize): void
    {
        $blob = trim(implode(' ', $prices).' '.($packSize ?? ''));
        if ($blob === '') {
            return;
        }

        if (preg_match('/unit\s*price[^0-9৳]*৳?\s*([\d,]+(?:\.\d+)?)/i', $blob, $m)) {
            $product['unit_price'] = '৳ '.$m[1];
            $product['numeric_unit_price'] = (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/strip\s*price[^0-9৳]*৳?\s*([\d,]+(?:\.\d+)?)/i', $blob, $m)) {
            $product['strip_price'] = '৳ '.$m[1];
            $product['numeric_strip_price'] = (float) str_replace(',', '', $m[1]);
        }

        if (empty($product['numeric_unit_price']) && preg_match('/৳\s*([\d,]+(?:\.\d+)?)/', $blob, $m)) {
            $product['unit_price'] = $product['unit_price'] ?? ('৳ '.$m[1]);
            $product['numeric_unit_price'] = (float) str_replace(',', '', $m[1]);
        }
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? '';
        $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $clean) ?? '';
        $clean = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/iu', '', $clean) ?? '';
        $clean = preg_replace('/javascript:/iu', '', $clean) ?? '';
        $clean = strip_tags($clean, '<p><br><ul><ol><li><strong><em><b><i><table><thead><tbody><tr><th><td>');

        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }
}
