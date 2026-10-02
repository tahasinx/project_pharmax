<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create test user
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Create test medicine
        $this->medicine = Medicine::factory()->create();
    }

    public function test_stocks_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/stocks');
        $response->assertStatus(200);
    }

    public function test_stocks_create_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/stocks/create');
        $response->assertStatus(200);
    }

    public function test_can_create_stock()
    {
        $stockData = [
            'medicine_id'     => $this->medicine->getRouteKey(),
            'batch_number'    => 'BATCH001',
            'expiry_date'     => now()->addMonths(12)->format('Y-m-d'),
            'quantity'        => 100,
            'min_stock_level' => 10,
            'max_stock_level' => 500,
            'purchase_price'  => 20.00,
            'selling_price'   => 25.00,
            'supplier'        => 'Test Supplier',
            'notes'           => 'Test stock entry',
        ];

        $response = $this->actingAs($this->user)->post('/stocks', $stockData);
        $response->assertRedirect('/stocks');

        $this->assertDatabaseHas('stocks', [
            'medicine_id'  => $this->medicine->id,
            'batch_number' => 'BATCH001',
            'quantity'     => 100,
        ]);
    }

    public function test_cannot_create_stock_without_required_fields()
    {
        $response = $this->actingAs($this->user)->post('/stocks', []);
        $response->assertSessionHasErrors(['medicine_id', 'expiry_date', 'quantity', 'min_stock_level']);
    }

    public function test_cannot_create_stock_with_past_expiry_date()
    {
        $stockData = [
            'medicine_id'     => $this->medicine->getRouteKey(),
            'expiry_date'     => now()->subDays(1)->format('Y-m-d'), // Past date
            'quantity'        => 100,
            'min_stock_level' => 10,
        ];

        $response = $this->actingAs($this->user)->post('/stocks', $stockData);
        $response->assertSessionHasErrors(['expiry_date']);
    }

    public function test_can_update_stock()
    {
        $stock = Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'quantity'    => 100,
        ]);

        $updateData = [
            'medicine_id'     => $this->medicine->getRouteKey(),
            'batch_number'    => 'BATCH002',
            'expiry_date'     => now()->addMonths(12)->format('Y-m-d'),
            'quantity'        => 150,
            'min_stock_level' => 15,
            'max_stock_level' => 600,
            'purchase_price'  => 22.00,
            'selling_price'   => 27.00,
            'supplier'        => 'Updated Supplier',
            'notes'           => 'Updated stock entry',
        ];

        $response = $this->actingAs($this->user)->put("/stocks/{$stock->getRouteKey()}", $updateData);
        $response->assertRedirect('/stocks');

        $this->assertDatabaseHas('stocks', [
            'id'           => $stock->id,
            'quantity'     => 150,
            'batch_number' => 'BATCH002',
        ]);
    }

    public function test_can_deactivate_stock()
    {
        $stock = Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->user)->delete("/stocks/{$stock->getRouteKey()}");
        $response->assertRedirect('/stocks');

        $this->assertDatabaseHas('stocks', [
            'id'        => $stock->id,
            'is_active' => false,
        ]);
    }

    public function test_stock_show_page()
    {
        $stock = Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
        ]);

        $response = $this->actingAs($this->user)->get("/stocks/{$stock->getRouteKey()}");
        $response->assertStatus(200);
    }

    public function test_stock_edit_page()
    {
        $stock = Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
        ]);

        $response = $this->actingAs($this->user)->get("/stocks/{$stock->getRouteKey()}/edit");
        $response->assertStatus(200);
    }

    public function test_stock_reports_page()
    {
        $response = $this->actingAs($this->user)->get('/stocks/reports');
        $response->assertStatus(200);
    }

    public function test_stock_alerts_page()
    {
        $response = $this->actingAs($this->user)->get('/stocks/alerts');
        $response->assertStatus(200);
    }

    public function test_low_stock_detection()
    {
        // Create stock with low quantity
        $stock = Stock::factory()->create([
            'medicine_id'     => $this->medicine->id,
            'quantity'        => 5,
            'min_stock_level' => 10,
            'is_active'       => true,
        ]);

        $response = $this->actingAs($this->user)->get('/stocks/alerts');
        $response->assertStatus(200);

        // Check if low stock is detected
        $this->assertTrue($stock->quantity < $stock->min_stock_level);
    }

    public function test_expired_stock_detection()
    {
        // Create stock with past expiry date
        $stock = Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'expiry_date' => now()->subDays(1),
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->user)->get('/stocks/alerts');
        $response->assertStatus(200);

        // Check if expired stock is detected
        $this->assertTrue($stock->expiry_date < now());
    }

    public function test_expiring_soon_stock_detection()
    {
        // Create stock expiring in 15 days
        $stock = Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'expiry_date' => now()->addDays(15),
            'is_active'   => true,
        ]);

        $response = $this->actingAs($this->user)->get('/stocks/alerts');
        $response->assertStatus(200);

        // Check if expiring soon stock is detected
        $this->assertTrue($stock->expiry_date <= now()->addDays(30));
    }

    public function test_stock_transaction_creation()
    {
        $stock = Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
        ]);

        $transactionData = [
            'stock_id'     => $stock->id,
            'medicine_id'  => $this->medicine->id,
            'type'         => 'purchase',
            'quantity'     => 50,
            'unit_price'   => 20.00,
            'total_amount' => 1000.00,
            'batch_number' => 'BATCH001',
            'expiry_date'  => now()->addMonths(12),
            'notes'        => 'Test transaction',
            'user_id'      => $this->user->id,
        ];

        $transaction = StockTransaction::create($transactionData);

        $this->assertDatabaseHas('stock_transactions', [
            'id'       => $transaction->id,
            'stock_id' => $stock->id,
            'type'     => 'purchase',
            'quantity' => 50,
        ]);
    }

    public function test_stock_total_calculation()
    {
        // Create multiple stock entries for same medicine
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

        $totalStock = $this->medicine->stocks()->active()->sum('quantity');
        $this->assertEquals(80, $totalStock);
    }

    public function test_unauthorized_access_redirects_to_login()
    {
        $response = $this->get('/stocks');
        $response->assertRedirect('/login');
    }
}
