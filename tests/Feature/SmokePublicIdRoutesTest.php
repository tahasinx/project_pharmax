<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmokePublicIdRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_guest_is_redirected_from_app_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/medicines')->assertRedirect('/login');
        $this->get('/invoices')->assertRedirect('/login');
        $this->get('/stocks')->assertRedirect('/login');
    }

    public function test_auth_pages_and_core_indexes_load(): void
    {
        $this->get('/login')->assertOk();

        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertOk();

        foreach ([
            '/medicines',
            '/medicines/create',
            '/categories',
            '/manufacturers',
            '/customers',
            '/stocks',
            '/stocks/create',
            '/invoices',
            '/invoices/create',
            '/pos',
            '/purchases',
            '/accounts',
            '/reports',
            '/settings',
            '/profile',
        ] as $path) {
            $this->actingAs($this->user)->get($path)->assertOk();
        }
    }

    public function test_public_uuid_route_binding_for_core_resources(): void
    {
        $category     = Category::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $medicine     = Medicine::factory()->create([
            'category_id'     => $category->id,
            'manufacturer_id' => $manufacturer->id,
        ]);
        $customer = Customer::factory()->create();
        $stock    = Stock::factory()->create(['medicine_id' => $medicine->id]);
        $invoice  = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
        ]);

        foreach ([
            $category->getRouteKey(),
            $manufacturer->getRouteKey(),
            $medicine->getRouteKey(),
            $customer->getRouteKey(),
            $stock->getRouteKey(),
            $this->user->getRouteKey(),
        ] as $key) {
            $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', (string) $key);
        }

        $this->assertNotEquals((string) $invoice->id, (string) $invoice->getRouteKey());

        $this->actingAs($this->user)->get('/medicines/'.$medicine->getRouteKey())->assertOk();
        $this->actingAs($this->user)->get('/medicines/'.$medicine->getRouteKey().'/edit')->assertOk();
        $this->actingAs($this->user)->get('/stocks/'.$stock->getRouteKey())->assertOk();
        $this->actingAs($this->user)->get('/stocks/'.$stock->getRouteKey().'/edit')->assertOk();
        $this->actingAs($this->user)->get('/invoices/'.$invoice->getRouteKey())->assertOk();
        $this->actingAs($this->user)->get('/customers/'.$customer->getRouteKey().'/edit')->assertOk();
        $this->actingAs($this->user)->get('/categories/'.$category->getRouteKey().'/edit')->assertOk();
        $this->actingAs($this->user)->get('/manufacturers/'.$manufacturer->getRouteKey().'/edit')->assertOk();

        // Numeric PK must not resolve public-id routes
        $this->actingAs($this->user)->get('/medicines/'.$medicine->id)->assertNotFound();
    }
}
