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

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create test user
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Create test data
        $this->category = Category::factory()->create(['name' => 'Test Category']);
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
        $medicineData = [
            'name' => 'Test Medicine',
            'medicine_type_id' => MedicineType::create(['name' => 'Allopathic'])->id,
            'generic_id' => Generic::create(['name' => 'Paracetamol'])->id,
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'generic_name' => 'Test Generic',
            'strength' => '500mg',
            'box_size' => 10,
            'price' => 25.50,
            'manufacturer_price' => 20.00,
            'unit' => 'tablet',
            'details' => 'Test medicine details',
            'status' => true,
        ];

        $response = $this->actingAs($this->user)->post('/medicines', $medicineData);
        $response->assertRedirect('/medicines');

        $this->assertDatabaseHas('medicines', [
            'name' => 'Test Medicine',
            'category_id' => $this->category->id,
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
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $updateData = [
            'name' => 'Updated Medicine',
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
            'medicine_type_id' => MedicineType::firstOrCreate(['name' => 'Allopathic'])->id,
            'generic_id' => Generic::firstOrCreate(['name' => 'Paracetamol'])->id,
            'strength' => '500mg',
            'unit' => 'Piece',
            'price' => 30.00,
            'manufacturer_price' => 25.00,
            'box_size' => 15,
        ];

        $response = $this->actingAs($this->user)->put("/medicines/{$medicine->id}", $updateData);
        $response->assertRedirect('/medicines');

        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'name' => 'Updated Medicine',
            'price' => 30.00,
        ]);
    }

    public function test_can_delete_medicine()
    {
        $medicine = Medicine::factory()->create([
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/medicines/{$medicine->id}");
        $response->assertRedirect('/medicines');

        $this->assertDatabaseMissing('medicines', ['id' => $medicine->id]);
    }

    public function test_medicine_import_functionality()
    {
        $csvContent = "name,generic_name,category,manufacturer,price\n";
        $csvContent .= "Test Medicine,Test Generic,Test Category,Test Manufacturer,25.50";

        $file = tmpfile();
        fwrite($file, $csvContent);
        rewind($file);

        $response = $this->actingAs($this->user)->post('/medicines/import', [
            'file' => $file
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
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->get("/medicines/{$medicine->id}/codes");
        $response->assertStatus(200);
    }

    public function test_medicine_show_page()
    {
        $medicine = Medicine::factory()->create([
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->get("/medicines/{$medicine->id}");
        $response->assertStatus(200);
    }

    public function test_medicine_edit_page()
    {
        $medicine = Medicine::factory()->create([
            'category_id' => $this->category->id,
            'manufacturer_id' => $this->manufacturer->id,
        ]);

        $response = $this->actingAs($this->user)->get("/medicines/{$medicine->id}/edit");
        $response->assertStatus(200);
    }

    public function test_unauthorized_access_redirects_to_login()
    {
        $response = $this->get('/medicines');
        $response->assertRedirect('/login');
    }
}
