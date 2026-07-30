<?php

namespace Tests\Feature;

use App\Models\Content\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_page_renders_with_no_events(): void
    {
        $this->get('/community')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Community')
                ->has('events', 0));
    }

    public function test_community_page_surfaces_upcoming_events_for_reuse(): void
    {
        Event::factory()->create(['title' => 'Community Meetup', 'type' => 'meetup', 'status' => 'upcoming']);
        Event::factory()->past()->create(['title' => 'Old Webinar']);

        $this->get('/community')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('events', 1, fn ($e) => $e
                    ->where('title', 'Community Meetup')
                    ->where('status', 'upcoming')
                    ->etc()));
    }
}
