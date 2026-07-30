<?php

namespace Tests\Feature;

use App\Models\Content\Lead;
use Database\Seeders\ProductCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_corporate_page_renders_with_real_tracks(): void
    {
        $this->seed(ProductCatalogueSeeder::class);

        $this->get('/corporate')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Corporate')
                ->has('tracks')
                ->where('tracks.0.slug', 'product-management'));
    }

    public function test_valid_corporate_inquiry_is_stored_with_optional_fields(): void
    {
        $response = $this->postJson(route('leads.corporate'), [
            'name' => '  Ada Lovelace  ',
            'email' => 'ada@acme.test',
            'company_name' => 'Acme Ltd',
            'employee_count' => '50-200',
            'message' => 'We want to upskill our analysts.',
            'job_title' => 'L&D Manager',
            'phone' => '+234 800 000 0000',
            'preferred_track' => 'Data Analysis',
            'start_timeframe' => '1–3 months',
            'country' => 'Nigeria',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $lead = Lead::where('type', 'corporate_inquiry')->firstOrFail();
        $this->assertSame('ada@acme.test', $lead->email);
        $this->assertSame('L&D Manager', $lead->metadata['job_title']);
        $this->assertSame('Data Analysis', $lead->metadata['preferred_track']);
        $this->assertSame('Nigeria', $lead->metadata['country']);
    }

    public function test_honeypot_submission_is_silently_dropped(): void
    {
        $response = $this->postJson(route('leads.corporate'), [
            'name' => 'Spam Bot',
            'email' => 'bot@spam.test',
            'company_name' => 'Spam Co',
            'message' => 'buy now',
            'website' => 'http://spam.example',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_corporate_inquiry_requires_core_fields(): void
    {
        // This app renders JSON errors only for api/* (see bootstrap/app.php), so
        // public form validation failures redirect back with session errors.
        $response = $this->from('/corporate')->post(route('leads.corporate'), [
            'name' => 'No Email',
            'company_name' => 'Acme',
            'message' => 'hi',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('leads', 0);
    }
}
