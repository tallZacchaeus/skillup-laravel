<?php

namespace Tests\Feature;

use App\Models\Content\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders(): void
    {
        $this->get('/contact')->assertOk()->assertInertia(fn ($page) => $page->component('Public/Contact'));
    }

    public function test_valid_contact_message_is_stored(): void
    {
        $this->postJson(route('leads.contact'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@acme.test',
            'phone' => '+234 800 000 0000',
            'enquiry_type' => 'Course enquiry',
            'subject' => 'Data track question',
            'message' => 'I would like to know more about your courses.',
        ])->assertOk()->assertJson(['success' => true]);

        $lead = Lead::where('email', 'ada@acme.test')->firstOrFail();
        $this->assertSame('contact_page', $lead->type);
        $this->assertSame('Course enquiry', $lead->metadata['enquiry_type']);
        $this->assertSame('Data track question', $lead->metadata['subject']);
    }

    public function test_honeypot_contact_submission_is_dropped(): void
    {
        $this->postJson(route('leads.contact'), [
            'name' => 'Spam Bot',
            'email' => 'bot@spam.test',
            'message' => 'buy now',
            'website' => 'http://spam.example',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_contact_requires_core_fields(): void
    {
        // JSON errors only for api/* (bootstrap/app.php), so web validation redirects with errors.
        $this->from('/contact')->post(route('leads.contact'), ['name' => 'No Email'])
            ->assertSessionHasErrors(['email', 'message']);
        $this->assertDatabaseCount('leads', 0);
    }
}
