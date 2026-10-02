<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicineTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $category;

    protected $manufacturer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->category     = Category::factory()->create(['name' => 'Test Category']);
        $this->manufacturer = Manufacturer::factory()->create(['name' => 'Test Manufacturer']);
    }

    public function test_medicines_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/medicines');
        $response->assertStatus(200);
    }

    public function test_medicines_create_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/medicines/create');
        $response->assertStatus(200);
    }

    public function test_can_create_medicine()
    {
        $type    = MedicineType::firstOrCreate(['name' => 'Allopathic']);
        $generic = Generic::firstOrCreate(['name' => 'Paracetamol']);

        $medicineData = [
            'name'               => 'Test Medicine',
            'medicine_type_id'   => $type->getRouteKey(),
            'generic_id'         => $generic->getRouteKey(),
            'category_id'        => $this->category->getRouteKey(),
            'manufacturer_id'    => $this->manufacturer->getRouteKey(),
            'generic_name'       => 'Test Generic',
            'strength'           => '500mg',
            'box_size'           => 10,
            'price'              => 25.50,
            'manufacturer_price' => 20.00,
            'unit'               => 'tablet',
            'details'            => 'Test medicine details',
            'status'             => true,
        ];

        $response = $this->actingAs($this->user)->post('/medicines', $medicineData);
        $response->assertRedirect('/medicines');

        $this->assertDatabaseHas('medicines', [
            'name'            => 'Test Medicine',
            'category_id'     => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);
    }

    public function test_cannot_create_medicine_without_required_fields()
    {
        $response = $this->actingAs($this->user)->post('/medicines', []);
        $response->assertSessionHasErrors(['name', 'generic_id', 'strength', 'medicine_type_id', 'unit', 'price', 'manufacturer_price']);
    }

    public function test_can_update_medicine()
    {
        $medicine = Medicine::factory()->create([
            'category_id'     => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);
        $type    = MedicineType::firstOrCreate(['name' => 'Allopathic']);
        $generic = Generic::firstOrCreate(['name' => 'Paracetamol']);

        $updateData = [
            'name'               => 'Updated Medicine',
            'category_id'        => $this->category->getRouteKey(),
            'manufacturer_id'    => $this->manufacturer->getRouteKey(),
            'medicine_type_id'   => $type->getRouteKey(),
            'generic_id'         => $generic->getRouteKey(),
            'strength'           => '500mg',
            'unit'               => 'Piece',
            'price'              => 30.00,
            'manufacturer_price' => 25.00,
            'box_size'           => 15,
        ];

        $response = $this->actingAs($this->user)->put('/medicines/'.$medicine->getRouteKey(), $updateData);
        $response->assertRedirect('/medicines');

        $this->assertDatabaseHas('medicines', [
            'id'    => $medicine->id,
            'name'  => 'Updated Medicine',
            'price' => 30.00,
        ]);
    }

    public function test_can_delete_medicine()
    {
        $medicine = Medicine::factory()->create([
            'category_id'     => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->delete('/medicines/'.$medicine->getRouteKey());
        $response->assertRedirect('/medicines');

        $this->assertDatabaseMissing('medicines', ['id' => $medicine->id]);
    }

    public function test_medicine_import_functionality()
    {
        $csvContent = "name,generic_name,category,manufacturer,price\n";
        $csvContent .= 'Test Medicine,Test Generic,Test Category,Test Manufacturer,25.50';

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'medicines.csv',
            $csvContent
        );

        $response = $this->actingAs($this->user)->post('/medicines/import', [
            'file' => $file,
        ]);

        $response->assertRedirect('/medicines');
        $this->assertDatabaseHas('medicines', ['name' => 'Test Medicine']);
    }

    public function test_medex_search_api()
    {
        $response = $this->actingAs($this->user)->get('/api/medex/search?q=paracetamol');
        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    public function test_medicine_codes_generation()
    {
        $medicine = Medicine::factory()->create([
            'category_id'     => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->get('/medicines/'.$medicine->getRouteKey().'/codes');
        $response->assertStatus(200);
    }

    public function test_medicine_show_page()
    {
        $medicine = Medicine::factory()->create([
            'category_id'     => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->get('/medicines/'.$medicine->getRouteKey());
        $response->assertStatus(200);
    }

    public function test_medicine_edit_page()
    {
        $medicine = Medicine::factory()->create([
            'category_id'     => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->get('/medicines/'.$medicine->getRouteKey().'/edit');
        $response->assertStatus(200);
    }

    public function test_unauthorized_access_redirects_to_login()
    {
        $response = $this->get('/medicines');
        $response->assertRedirect('/login');
    }
}
