<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\InvoiceItem;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineTest extends TestCase
{
    use RefreshDatabase;

    protected $category;

    protected $manufacturer;

    protected $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category     = Category::factory()->create();
        $this->manufacturer = Manufacturer::factory()->create();
        $this->medicine     = Medicine::factory()->create([
            'category_id'     => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);
    }

    public function test_medicine_belongs_to_category()
    {
        $this->assertInstanceOf(Category::class, $this->medicine->category);
        $this->assertEquals($this->category->id, $this->medicine->category->id);
    }

    public function test_medicine_belongs_to_manufacturer()
    {
        $this->assertInstanceOf(Manufacturer::class, $this->medicine->manufacturer);
        $this->assertEquals($this->manufacturer->id, $this->medicine->manufacturer->id);
    }

    public function test_medicine_has_stocks()
    {
        Stock::factory()->create(['medicine_id' => $this->medicine->id]);
        Stock::factory()->create(['medicine_id' => $this->medicine->id]);

        $this->assertCount(2, $this->medicine->stocks);
    }

    public function test_medicine_has_invoice_items()
    {
        InvoiceItem::factory()->create(['medicine_id' => $this->medicine->id]);
        InvoiceItem::factory()->create(['medicine_id' => $this->medicine->id]);

        $this->assertCount(2, $this->medicine->invoiceItems);
    }

    public function test_total_stock_calculation()
    {
        Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'quantity'    => 50,
            'is_active'   => true,
        ]);

        Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'quantity'    => 30,
            'is_active'   => true,
        ]);

        $this->assertEquals(80, $this->medicine->getTotalStockAttribute());
    }

    public function test_low_stock_detection()
    {
        // Create stock with low quantity
        Stock::factory()->create([
            'medicine_id'     => $this->medicine->id,
            'quantity'        => 5,
            'min_stock_level' => 10,
            'is_active'       => true,
        ]);

        $this->assertTrue($this->medicine->isLowStock());
    }

    public function test_not_low_stock_when_sufficient()
    {
        // Create stock with sufficient quantity
        Stock::factory()->create([
            'medicine_id'     => $this->medicine->id,
            'quantity'        => 50,
            'min_stock_level' => 10,
            'is_active'       => true,
        ]);

        $this->assertFalse($this->medicine->isLowStock());
    }

    public function test_available_stocks_query()
    {
        Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'quantity'    => 50,
            'is_active'   => true,
        ]);

        Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'quantity'    => 0, // No stock
            'is_active'   => true,
        ]);

        $availableStocks = $this->medicine->getAvailableStocks()->get();
        $this->assertCount(1, $availableStocks);
    }

    public function test_sufficient_stock_check()
    {
        Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'quantity'    => 50,
            'is_active'   => true,
        ]);

        $this->assertTrue($this->medicine->hasSufficientStock(30));
        $this->assertFalse($this->medicine->hasSufficientStock(60));
    }

    public function test_average_purchase_price_calculation()
    {
        Stock::factory()->create([
            'medicine_id'    => $this->medicine->id,
            'quantity'       => 50,
            'purchase_price' => 20.00,
            'is_active'      => true,
        ]);

        Stock::factory()->create([
            'medicine_id'    => $this->medicine->id,
            'quantity'       => 30,
            'purchase_price' => 25.00,
            'is_active'      => true,
        ]);

        $averagePrice    = $this->medicine->getAveragePurchasePriceAttribute();
        $expectedAverage = (50 * 20.00 + 30 * 25.00) / 80; // 21.875

        $this->assertEquals($expectedAverage, $averagePrice);
    }

    public function test_average_purchase_price_fallback_to_manufacturer_price()
    {
        $this->medicine->update(['manufacturer_price' => 15.00]);

        // No active stocks
        $averagePrice = $this->medicine->getAveragePurchasePriceAttribute();
        $this->assertEquals(15.00, $averagePrice);
    }

    public function test_medicine_fillable_attributes()
    {
        $fillable = $this->medicine->getFillable();

        foreach ([
            'product_id',
            'name',
            'category_id',
            'manufacturer_id',
            'medicine_id',
            'price',
            'status',
            'discount_percent',
            'alert_qty',
        ] as $column) {
            $this->assertContains($column, $fillable);
        }
    }

    public function test_medicine_casts()
    {
        $casts = $this->medicine->getCasts();

        $this->assertSame('decimal:2', $casts['price']);
        $this->assertSame('decimal:2', $casts['manufacturer_price']);
        $this->assertSame('decimal:2', $casts['discount_percent']);
        $this->assertSame('integer', $casts['box_size']);
        $this->assertSame('integer', $casts['alert_qty']);
        $this->assertSame('boolean', $casts['status']);
    }

    public function test_medicine_has_stock_transactions()
    {
        $stock = Stock::factory()->create(['medicine_id' => $this->medicine->id]);

        $this->assertInstanceOf(HasMany::class, $this->medicine->stockTransactions());
    }
}
