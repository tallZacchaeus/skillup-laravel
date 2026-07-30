<?php

namespace Tests\Feature;

use App\Models\Content\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_show_empty_state_when_none_scheduled(): void
    {
        $this->get('/events')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Events/Index')
                ->where('events', [])
                ->where('featuredEvent', null));
    }

    public function test_events_list_with_featured_and_formatted_meta(): void
    {
        Event::factory()->count(3)->create(['type' => 'webinar', 'status' => 'upcoming']);

        $this->get('/events')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('featuredEvent')
                ->has('events', 2)
                ->has('categories', 1)
                ->where('events.0.deliveryMode', 'Online')
                ->has('events.0.duration'));
    }

    public function test_events_search_and_type_filter(): void
    {
        Event::factory()->create(['title' => 'React Live Workshop', 'type' => 'workshop', 'status' => 'upcoming']);
        Event::factory()->create(['title' => 'Career Webinar', 'type' => 'webinar', 'status' => 'upcoming']);

        $this->get('/events?search=React')->assertOk()->assertInertia(fn ($page) => $page->where('pagination.total', 1));
        $this->get('/events?category=workshop')->assertOk()->assertInertia(fn ($page) => $page->where('pagination.total', 1));
    }

    public function test_registration_captures_a_registrant(): void
    {
        Notification::fake();
        $event = Event::factory()->create(['slug' => 'live-webinar', 'status' => 'upcoming']);

        $this->post(route('events.register', 'live-webinar'), ['name' => 'Ada', 'email' => 'ada@test.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('event_registrations', ['event_id' => $event->id, 'email' => 'ada@test.com']);
        $this->assertSame(1, $event->fresh()->registered_count);
    }

    public function test_duplicate_registration_is_rejected(): void
    {
        Notification::fake();
        Event::factory()->create(['slug' => 'live-webinar', 'status' => 'upcoming']);

        $payload = ['name' => 'Ada', 'email' => 'ada@test.com'];
        $this->post(route('events.register', 'live-webinar'), $payload)->assertRedirect();

        $this->from('/events/live-webinar')
            ->post(route('events.register', 'live-webinar'), $payload)
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('event_registrations', 1);
    }
}
