<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $customer;
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

        // Create test data
        $this->customer = Customer::factory()->create(['name' => 'Test Customer']);
        $this->medicine = Medicine::factory()->create();

        // Create stock for the medicine
        Stock::factory()->create([
            'medicine_id' => $this->medicine->id,
            'quantity' => 100,
            'selling_price' => 25.00,
        ]);
    }

    public function test_invoices_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/invoices');
        $response->assertStatus(200);
    }

    public function test_invoices_create_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/invoices/create');
        $response->assertStatus(200);
    }

    public function test_pos_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/pos');
        $response->assertStatus(200);
    }

    public function test_can_create_invoice()
    {
        $invoiceData = [
            'customer_id' => $this->customer->id,
            'date' => now()->format('Y-m-d'),
            'payment_type' => 'cash',
            'paid_amount' => 50.00,
            'due_amount' => 0.00,
            'total_amount' => 50.00,
            'total_tax' => 5.00,
            'total_discount' => 0.00,
            'items' => [
                [
                    'medicine_id' => $this->medicine->id,
                    'quantity' => 2,
                    'rate' => 25.00,
                    'discount' => 0,
                    'batch_id' => 'BATCH001',
                ]
            ],
            'send_sms' => false,
            'send_email' => false,
        ];

        $response = $this->actingAs($this->user)->post('/invoices', $invoiceData);
        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $this->customer->id,
            'total_amount' => 50.00,
        ]);
    }

    public function test_cannot_create_invoice_without_customer()
    {
        $response = $this->actingAs($this->user)->post('/invoices', [
            'date' => now()->format('Y-m-d'),
            'total_amount' => 50.00,
        ]);

        $response->assertSessionHasErrors(['customer_id']);
    }

    public function test_cannot_create_invoice_without_items()
    {
        $response = $this->actingAs($this->user)->post('/invoices', [
            'customer_id' => $this->customer->id,
            'date' => now()->format('Y-m-d'),
            'total_amount' => 50.00,
        ]);

        $response->assertSessionHasErrors(['items']);
    }

    public function test_cannot_create_invoice_with_insufficient_stock()
    {
        $invoiceData = [
            'customer_id' => $this->customer->id,
            'date' => now()->format('Y-m-d'),
            'payment_type' => 'cash',
            'total_amount' => 5000.00,
            'items' => [
                [
                    'medicine_id' => $this->medicine->id,
                    'quantity' => 1000, // More than available stock
                    'rate' => 25.00,
                    'discount' => 0,
                    'batch_id' => 'BATCH001',
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->post('/invoices', $invoiceData);
        $response->assertSessionHasErrors(['items']);
    }

    public function test_can_update_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'total_amount' => 50.00,
        ]);

        $updateData = [
            'customer_id' => $this->customer->id,
            'date' => now()->format('Y-m-d'),
            'payment_type' => 'bank',
            'total_amount' => 75.00,
            'items' => [
                [
                    'medicine_id' => $this->medicine->id,
                    'quantity' => 3,
                    'rate' => 25.00,
                    'discount' => 0,
                    'batch_id' => 'BATCH001',
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->put("/invoices/{$invoice->id}", $updateData);
        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'total_amount' => 75.00,
            'payment_type' => 'bank',
        ]);
    }

    public function test_can_delete_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/invoices/{$invoice->id}");
        $response->assertRedirect('/invoices');

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_invoice_show_page()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->user)->get("/invoices/{$invoice->id}");
        $response->assertStatus(200);
    }

    public function test_invoice_print_page()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->user)->get("/invoices/{$invoice->id}/print");
        $response->assertStatus(200);
    }

    public function test_medicine_search_api()
    {
        $response = $this->actingAs($this->user)->get('/api/medicines/search?q=test');
        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    public function test_customer_search_api()
    {
        $response = $this->actingAs($this->user)->get('/api/customers/search?q=test');
        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    public function test_available_stocks_api()
    {
        $response = $this->actingAs($this->user)->get("/api/medicines/{$this->medicine->id}/stocks");
        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    public function test_invoice_creation_updates_stock()
    {
        $initialStock = Stock::where('medicine_id', $this->medicine->id)->first();
        $initialQuantity = $initialStock->quantity;

        $invoiceData = [
            'customer_id' => $this->customer->id,
            'date' => now()->format('Y-m-d'),
            'payment_type' => 'cash',
            'total_amount' => 25.00,
            'items' => [
                [
                    'medicine_id' => $this->medicine->id,
                    'quantity' => 1,
                    'rate' => 25.00,
                    'discount' => 0,
                    'batch_id' => 'BATCH001',
                ]
            ],
        ];

        $this->actingAs($this->user)->post('/invoices', $invoiceData);

        $updatedStock = Stock::where('medicine_id', $this->medicine->id)->first();
        $this->assertEquals($initialQuantity - 1, $updatedStock->quantity);
    }

    public function test_invoice_creation_creates_stock_transaction()
    {
        $invoiceData = [
            'customer_id' => $this->customer->id,
            'date' => now()->format('Y-m-d'),
            'payment_type' => 'cash',
            'total_amount' => 25.00,
            'items' => [
                [
                    'medicine_id' => $this->medicine->id,
                    'quantity' => 1,
                    'rate' => 25.00,
                    'discount' => 0,
                    'batch_id' => 'BATCH001',
                ]
            ],
        ];

        $this->actingAs($this->user)->post('/invoices', $invoiceData);

        $this->assertDatabaseHas('stock_transactions', [
            'medicine_id' => $this->medicine->id,
            'type' => 'sale',
            'quantity' => -1, // Negative for sale
        ]);
    }
}
