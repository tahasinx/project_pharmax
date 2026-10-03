<?php

namespace App\Domain\Medex\Parsers;

use App\Domain\Medex\MedexClient;
use Symfony\Component\DomCrawler\Crawler;

class SearchParser
{
    public function __construct(private readonly MedexClient $client) {}

    /**
     * @return list<array{link: ?string, form: ?string, name: ?string, strength: ?string, img: ?string}>
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
            $results[]    = [
                'link'     => $this->client->absolute($node->attr('href')),
                'form'     => $formNode->count() ? $formNode->attr('title') : null,
                'name'     => $spanNode->count() ? trim($spanNode->text()) : null,
                'strength' => $strengthNode->count() ? trim($strengthNode->text()) : null,
                'img'      => $this->client->absolute($imgNode->count() ? $imgNode->attr('src') : null),
            ];
        });

        return $results;
    }
}
