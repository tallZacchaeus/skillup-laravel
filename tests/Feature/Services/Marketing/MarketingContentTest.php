<?php

namespace Tests\Feature\Services\Marketing;

use App\Jobs\Notifications\SendEmailMessageJob;
use App\Models\Content\Downloadable;
use App\Models\Content\Event;
use App\Models\Content\EventRegistration;
use App\Models\Content\Lead;
use App\Models\Content\ResourceCategory;
use App\Models\Notifications\EmailMessage;
use App\Notifications\EventRegisteredNotification;
use App\Notifications\NewLeadNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MarketingContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsletter_lead_submission()
    {
        $response = $this->postJson(route('leads.newsletter'), [
            'email' => 'subscriber@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'email' => 'subscriber@example.com',
            'type' => 'newsletter',
        ]);
    }

    public function test_contact_lead_submission_triggers_admin_notification()
    {
        Notification::fake();

        $response = $this->postJson(route('leads.contact'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'message' => 'Hello there, I have a question.',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'type' => 'contact_page',
        ]);

        Notification::assertSentOnDemand(
            NewLeadNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === config('mail.from.address') &&
                    $notification->lead->email === 'john@example.com';
            }
        );
    }

    public function test_corporate_lead_submission_triggers_admin_notification()
    {
        Notification::fake();

        $response = $this->postJson(route('leads.corporate'), [
            'name' => 'Alice Corporate',
            'email' => 'alice@company.com',
            'company_name' => 'Corporate Inc',
            'employee_count' => '50-200',
            'message' => 'We need to train 100 developers.',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'email' => 'alice@company.com',
            'name' => 'Alice Corporate',
            'type' => 'corporate_inquiry',
        ]);

        Notification::assertSentOnDemand(
            NewLeadNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notification->lead->email === 'alice@company.com';
            }
        );
    }

    public function test_resource_download_increments_count_and_captures_lead()
    {
        Storage::fake('public');
        Storage::disk('public')->put('resources-files/test.pdf', 'dummy content');

        $category = ResourceCategory::create(['name' => 'Ebooks', 'slug' => 'ebooks']);
        $downloadable = Downloadable::create([
            'resource_category_id' => $category->id,
            'title' => 'Laravel Security Guide',
            'slug' => 'laravel-security-guide',
            'description' => 'A guide to secure Laravel apps.',
            'file_path' => 'resources-files/test.pdf',
            'status' => 'published',
        ]);

        $response = $this->post(route('resources.download', ['slug' => $downloadable->slug]), [
            'name' => 'Bob Builder',
            'email' => 'bob@example.com',
            'phone' => '999999999',
        ]);

        $response->assertOk(); // File download response is OK
        
        $this->assertDatabaseHas('leads', [
            'email' => 'bob@example.com',
            'type' => 'downloadable_resource',
        ]);

        $this->assertEquals(1, $downloadable->fresh()->download_count);
    }

    public function test_resource_detail_page_serves_published_resource()
    {
        $category = ResourceCategory::create(['name' => 'Templates', 'slug' => 'templates']);
        $downloadable = Downloadable::create([
            'resource_category_id' => $category->id,
            'title' => 'Career Roadmap',
            'slug' => 'career-roadmap',
            'description' => 'A roadmap for choosing a tech track.',
            'file_path' => 'resources-files/roadmap.pdf',
            'status' => 'published',
        ]);

        $this->get(route('resources.show', ['slug' => $downloadable->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Resources/Show')
                ->where('resource.slug', 'career-roadmap')
                ->where('resource.category.name', 'Templates'));
    }

    public function test_resource_download_does_not_capture_lead_when_file_is_missing()
    {
        Storage::fake('public');

        $category = ResourceCategory::create(['name' => 'Ebooks', 'slug' => 'ebooks']);
        $downloadable = Downloadable::create([
            'resource_category_id' => $category->id,
            'title' => 'Missing File Guide',
            'slug' => 'missing-file-guide',
            'description' => 'This file has not been uploaded.',
            'file_path' => 'resources-files/missing.pdf',
            'status' => 'published',
        ]);

        $this->post(route('resources.download', ['slug' => $downloadable->slug]), [
            'name' => 'Missing User',
            'email' => 'missing@example.com',
        ])->assertNotFound();

        $this->assertDatabaseMissing('leads', [
            'email' => 'missing@example.com',
            'type' => 'downloadable_resource',
        ]);
        $this->assertEquals(0, $downloadable->fresh()->download_count);
    }

    public function test_event_registration_limits_and_notifications()
    {
        Notification::fake();

        $event = Event::create([
            'title' => 'Laravel Masterclass',
            'slug' => 'laravel-masterclass',
            'description' => 'Learn Laravel core concepts.',
            'type' => 'webinar',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'registration_limit' => 2,
            'registered_count' => 0,
            'status' => 'upcoming',
        ]);

        // Register First seat
        $response = $this->post(route('events.register', ['slug' => $event->slug]), [
            'name' => 'Participant One',
            'email' => 'p1@example.com',
            'phone' => '1111111',
        ]);
        $response->assertRedirect();
        $this->assertEquals(1, $event->fresh()->registered_count);

        // Register Second seat
        $response = $this->post(route('events.register', ['slug' => $event->slug]), [
            'name' => 'Participant Two',
            'email' => 'p2@example.com',
            'phone' => '2222222',
        ]);
        $response->assertRedirect();
        $this->assertEquals(2, $event->fresh()->registered_count);

        // Register Third seat (should fail because limit is 2)
        $response = $this->post(route('events.register', ['slug' => $event->slug]), [
            'name' => 'Participant Three',
            'email' => 'p3@example.com',
            'phone' => '3333333',
        ]);
        $response->assertSessionHasErrors(['message']);
        $this->assertEquals(2, $event->fresh()->registered_count);

        // Check registration notification sent to Participant One
        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable(),
            EventRegisteredNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'p1@example.com' &&
                    $notification->event->slug === 'laravel-masterclass';
            }
        );
    }

    public function test_event_registration_uses_actual_count_when_cached_count_is_stale()
    {
        $event = Event::create([
            'title' => 'Capacity Test',
            'slug' => 'capacity-test',
            'description' => 'Capacity should use real registrations.',
            'type' => 'webinar',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'registration_limit' => 1,
            'registered_count' => 0,
            'status' => 'upcoming',
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'name' => 'Existing Seat',
            'email' => 'existing@example.com',
        ]);

        $event->updateQuietly(['registered_count' => 0]);

        $this->post(route('events.register', ['slug' => $event->slug]), [
            'name' => 'Second Seat',
            'email' => 'second@example.com',
        ])->assertSessionHasErrors(['message']);

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'email' => 'second@example.com',
        ]);
    }

    public function test_event_registration_count_syncs_when_registration_records_change()
    {
        $event = Event::create([
            'title' => 'Count Sync',
            'slug' => 'count-sync',
            'description' => 'Registration count should stay in sync.',
            'type' => 'webinar',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'registration_limit' => 10,
            'registered_count' => 0,
            'status' => 'upcoming',
        ]);

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'name' => 'Counted User',
            'email' => 'counted@example.com',
        ]);

        $this->assertEquals(1, $event->fresh()->registered_count);

        $registration->delete();

        $this->assertEquals(0, $event->fresh()->registered_count);
    }

    public function test_contact_lead_notification_uses_phase9_email_pipeline()
    {
        Queue::fake();

        $this->postJson(route('leads.contact'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'message' => 'I need help choosing a course.',
        ])->assertOk();

        $emailMessage = EmailMessage::where('recipient_email', config('mail.from.address'))->first();

        $this->assertNotNull($emailMessage);
        $this->assertSame('queued', $emailMessage->status);
        $this->assertStringContainsString('New Marketing Lead Capture', $emailMessage->subject);

        Queue::assertPushed(SendEmailMessageJob::class, function ($job) use ($emailMessage) {
            return $job->emailMessage->is($emailMessage);
        });
    }

    public function test_event_registration_notification_uses_phase9_email_pipeline()
    {
        Queue::fake();

        $event = Event::create([
            'title' => 'Pipeline Webinar',
            'slug' => 'pipeline-webinar',
            'description' => 'Pipeline notification test.',
            'type' => 'webinar',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'registration_limit' => 10,
            'registered_count' => 0,
            'status' => 'upcoming',
        ]);

        $this->post(route('events.register', ['slug' => $event->slug]), [
            'name' => 'Pipeline User',
            'email' => 'pipeline@example.com',
        ])->assertRedirect();

        $emailMessage = EmailMessage::where('recipient_email', 'pipeline@example.com')->first();

        $this->assertNotNull($emailMessage);
        $this->assertSame('queued', $emailMessage->status);
        $this->assertStringContainsString('Registration Confirmed', $emailMessage->subject);

        Queue::assertPushed(SendEmailMessageJob::class, function ($job) use ($emailMessage) {
            return $job->emailMessage->is($emailMessage);
        });
    }
}
