<?php

namespace Tests\Unit;

use App\Domain\Medex\MedexClient;
use App\Domain\Medex\Parsers\BrandListParser;
use App\Domain\Medex\Parsers\CompanyParser;
use App\Domain\Medex\Parsers\DosageFormParser;
use App\Domain\Medex\Parsers\GenericParser;
use App\Domain\Medex\Parsers\ProductParser;
use PHPUnit\Framework\TestCase;

class MedexParserTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__.'/../fixtures/medex/'.$name);
    }

    public function test_dosage_form_parser_reads_brand_counts(): void
    {
        $rows = (new DosageFormParser)->parse($this->fixture('dosage-forms.html'));

        $this->assertCount(3, $rows);
        $this->assertSame('Tablet', $rows[0]['name']);
        $this->assertSame(4200, $rows[0]['brand_count']);
        $this->assertSame('tablet', $rows[0]['medex_slug']);
    }

    public function test_company_parser_reads_manufacturer_rows(): void
    {
        $client = new MedexClient(60);
        $rows = (new CompanyParser($client))->parse($this->fixture('companies.html'));

        $this->assertCount(2, $rows);
        $this->assertSame('Square Pharmaceuticals Ltd.', $rows[0]['name']);
        $this->assertSame('123', $rows[0]['medex_id']);
        $this->assertStringContainsString('1200 brands', (string) $rows[0]['details']);
    }

    public function test_generic_parser_reads_available_brands(): void
    {
        $client = new MedexClient(60);
        $rows = (new GenericParser($client))->parse($this->fixture('generics.html'));

        $names = array_column($rows, 'name');
        $this->assertContains('Paracetamol', $names);
        $this->assertContains('Amoxicillin', $names);

        $para = null;
        foreach ($rows as $row) {
            if ($row['name'] === 'Paracetamol') {
                $para = $row;
                break;
            }
        }
        $this->assertNotNull($para);
        $this->assertSame(85, $para['brand_count']);
        $this->assertSame('101', $para['medex_id']);
    }

    public function test_brand_list_parser_reads_cards(): void
    {
        $client = new MedexClient(60);
        $rows = (new BrandListParser($client))->parse($this->fixture('brands.html'));

        $this->assertCount(2, $rows);
        $this->assertSame('Napa', $rows[0]['name']);
        $this->assertSame('500 mg', $rows[0]['strength']);
        $this->assertSame('Paracetamol', $rows[0]['generic']);
        $this->assertSame('Beximco Pharmaceuticals Ltd.', $rows[0]['manufacturer']);
        $this->assertSame('Tablet', $rows[0]['form']);
        $this->assertSame('5555', $rows[0]['medex_id']);
        $this->assertSame('napa', $rows[0]['medex_slug']);
    }

    public function test_product_parser_reads_prices_and_sanitizes_html(): void
    {
        $client = new MedexClient(60);
        $product = (new ProductParser($client))->parse(
            $this->fixture('product.html'),
            'https://medex.com.bd/brands/5555/napa'
        );

        $this->assertStringContainsString('Napa', (string) $product['name']);
        $this->assertSame('Tablet', $product['form']);
        $this->assertSame('Paracetamol', $product['generic']);
        $this->assertSame('500 mg', $product['strength']);
        $this->assertSame(1.2, $product['numeric_unit_price']);
        $this->assertSame(12.0, $product['numeric_strip_price']);
        $this->assertSame('5555', $product['medex_id']);
        $this->assertSame('napa', $product['medex_name']);
        $this->assertStringContainsString('Fever and pain', (string) $product['indications']);
        $this->assertStringNotContainsString('<script>', (string) $product['indications']);
        $this->assertTrue($product['indications_html']);
        $this->assertStringContainsString('napa.png', (string) $product['img']);
    }

    public function test_medex_client_allows_only_medex_https_host(): void
    {
        $client = new MedexClient(60);
        $this->assertTrue($client->isAllowedUrl('https://medex.com.bd/brands/1/napa'));
        $this->assertFalse($client->isAllowedUrl('http://medex.com.bd/brands/1/napa'));
        $this->assertFalse($client->isAllowedUrl('https://evil.example/steal'));
        $this->assertFalse($client->isAllowedUrl('https://medex.com.bd.evil/x'));
        $this->assertNull($client->get('https://evil.example/steal', [], false));
    }
}
