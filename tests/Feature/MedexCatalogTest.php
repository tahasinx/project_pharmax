<?php

namespace Tests\Feature;

use App\Domain\Medex\MedexCatalogService;
use App\Jobs\SyncMedexDirectoryJob;
use App\Models\Medicine;
use App\Models\MedexBrandIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedexCatalogTest extends TestCase
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

    public function test_brand_index_import_does_not_create_medicines(): void
    {
        $before = Medicine::count();

        $result = app(MedexCatalogService::class)->importBrandIndex([
            [
                'name'         => 'Napa',
                'strength'     => '500 mg',
                'generic'      => 'Paracetamol',
                'manufacturer' => 'Beximco Pharmaceuticals Ltd.',
                'form'         => 'Tablet',
                'link'         => 'https://medex.com.bd/brands/5555/napa',
                'medex_path'   => '/brands/5555/napa',
                'medex_id'     => '5555',
                'medex_slug'   => 'napa',
            ],
        ], 'allopathic');

        $this->assertSame(1, $result['indexed']);
        $this->assertSame($before, Medicine::count());
        $this->assertDatabaseHas('medex_brand_indexes', [
            'medex_id' => '5555',
            'name'     => 'Napa',
            'segment'  => 'allopathic',
        ]);
        $this->assertSame(1, MedexBrandIndex::count());
    }

    public function test_medex_status_endpoint(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('api.medex.status'));
        $response->assertOk()
            ->assertJsonStructure(['enabled', 'last_sync', 'cache_ttl', 'counts']);
    }

    public function test_sync_queues_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user)->postJson(route('api.medex.sync', 'dosage-forms'), [
            'segment' => 'allopathic',
        ]);

        $response->assertOk()->assertJson(['queued' => true, 'directory' => 'dosage-forms']);
        Queue::assertPushed(SyncMedexDirectoryJob::class);
    }

    public function test_import_dosage_forms_endpoint(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('api.medex.dosage-forms.import'), [
            'rows' => [
                ['name' => 'Tablet', 'brand_count' => 10, 'medex_slug' => 'tablet'],
                ['name' => 'Syrup', 'brand_count' => 5, 'medex_slug' => 'syrup'],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('dosage_forms', ['name' => 'Tablet', 'medex_slug' => 'tablet']);
        $this->assertDatabaseHas('dosage_forms', ['name' => 'Syrup']);
    }
}
