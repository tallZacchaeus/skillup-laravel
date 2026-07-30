<?php

namespace Tests\Feature;

use App\Models\Content\Downloadable;
use App\Models\Content\Lead;
use App\Models\Content\ResourceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourcesIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_resources_show_empty_state_when_none_published(): void
    {
        $this->get('/resources')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Resources/Index')
                ->where('resources', [])
                ->where('featuredResource', null)
                ->where('pagination.total', 0));
    }

    public function test_resources_list_with_featured_and_derived_file_type(): void
    {
        $category = ResourceCategory::factory()->create(['name' => 'Guides', 'slug' => 'guides']);
        Downloadable::factory()->count(3)->create([
            'resource_category_id' => $category->id,
            'status' => 'published',
            'file_path' => 'resources/sample.pdf',
        ]);

        $this->get('/resources')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('featuredResource')
                ->has('resources', 2)
                ->has('categories', 1)
                ->where('pagination.total', 3)
                ->where('resources.0.fileType', 'PDF')
                ->where('resources.0.isGated', true));
    }

    public function test_resources_search_and_category_filter(): void
    {
        $guides = ResourceCategory::factory()->create(['slug' => 'guides']);
        $templates = ResourceCategory::factory()->create(['slug' => 'templates']);
        Downloadable::factory()->create(['title' => 'React Cheatsheet', 'resource_category_id' => $guides->id, 'status' => 'published']);
        Downloadable::factory()->create(['title' => 'Budget Template', 'resource_category_id' => $templates->id, 'status' => 'published']);

        $this->get('/resources?search=React')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('pagination.total', 1));

        $this->get('/resources?category=templates')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('pagination.total', 1));
    }

    public function test_gated_download_requires_email_and_captures_lead(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('resources/guide.pdf', 'PDF BYTES');
        $resource = Downloadable::factory()->create(['slug' => 'the-guide', 'status' => 'published', 'is_gated' => true, 'file_path' => 'resources/guide.pdf']);

        // Missing email -> rejected, no lead (web validation redirects with errors).
        $this->from('/resources')->post(route('resources.download', 'the-guide'), [])
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('leads', 0);

        // Valid email -> file download + lead captured.
        $this->post(route('resources.download', 'the-guide'), ['email' => 'lead@acme.test', 'name' => 'Lead'])
            ->assertOk()
            ->assertDownload();
        $this->assertSame(1, Lead::where('type', 'downloadable_resource')->count());
        $this->assertSame(1, $resource->fresh()->download_count);
    }

    public function test_ungated_download_works_without_email(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('resources/free.pdf', 'PDF BYTES');
        Downloadable::factory()->ungated()->create(['slug' => 'free-guide', 'status' => 'published', 'file_path' => 'resources/free.pdf']);

        $this->post(route('resources.download', 'free-guide'), [])
            ->assertOk()
            ->assertDownload();
        $this->assertDatabaseCount('leads', 0);
    }
}
