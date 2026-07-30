<?php

namespace Tests\Feature\Programs;

use App\Enums\PaymentStatus;
use App\Enums\ProgramRegistrationStatus;
use App\Jobs\Notifications\SendEmailMessageJob;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use App\Models\Programs\ProgramRegistration;
use App\Services\Programs\ProgramRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProgramRegistrationFunnelTest extends TestCase
{
    use RefreshDatabase;

    private Program $program;

    private ProgramEdition $edition;

    private ProgramEditionTrack $juniorTrack;

    private ProgramEditionTrack $teenTrack;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->program = Program::factory()->create(['slug' => 'summer-ai', 'name' => 'Summer AI']);
        $this->edition = ProgramEdition::factory()->create([
            'program_id' => $this->program->id,
            'starts_on' => now()->addMonths(2)->toDateString(),
            'age_reference_date' => null,
        ]);

        $product = Product::factory()->create();
        ProductPrice::factory()->create(['product_id' => $product->id, 'amount' => 100000, 'is_default' => true, 'is_active' => true]);
        $teenProduct = Product::factory()->create();
        ProductPrice::factory()->create(['product_id' => $teenProduct->id, 'amount' => 100000, 'is_default' => true, 'is_active' => true]);

        $this->juniorTrack = ProgramEditionTrack::factory()->create([
            'program_edition_id' => $this->edition->id,
            'product_id' => $product->id,
            'name' => 'Alpha AI',
            'slug' => 'alpha-ai',
            'age_min' => 8,
            'age_max' => 13,
            'capacity' => 2,
        ]);
        $this->teenTrack = ProgramEditionTrack::factory()->create([
            'program_edition_id' => $this->edition->id,
            'product_id' => $teenProduct->id,
            'name' => 'AI Explorer',
            'slug' => 'ai-explorer',
            'age_min' => 14,
            'age_max' => 18,
            'capacity' => 2,
        ]);
    }

    private function microForm(array $overrides = []): array
    {
        return array_merge([
            'guardian_name' => 'Ada Guardian',
            'guardian_email' => 'ada@example.com',
            'guardian_whatsapp' => '+2348011111111',
            'participant_name' => 'Kid Example',
            'participant_dob' => now()->subYears(10)->toDateString(),
        ], $overrides);
    }

    public function test_micro_form_creates_registration_matched_to_age_track_and_sends_verification(): void
    {
        $response = $this->post("/programs/{$this->program->slug}/register", $this->microForm());

        $registration = ProgramRegistration::firstOrFail();

        $response->assertRedirect(route('programs.registrations.verify.page', $registration->uuid));
        $this->assertEquals($this->juniorTrack->id, $registration->program_edition_track_id);
        $this->assertEquals(ProgramRegistrationStatus::Started, $registration->status);
        $this->assertNotNull($registration->email_verification_token);
        Queue::assertPushed(SendEmailMessageJob::class, 1);
    }

    public function test_teen_dob_selects_teen_track(): void
    {
        $this->post("/programs/{$this->program->slug}/register", $this->microForm([
            'participant_dob' => now()->subYears(16)->toDateString(),
        ]));

        $this->assertEquals($this->teenTrack->id, ProgramRegistration::firstOrFail()->program_edition_track_id);
    }

    public function test_age_outside_all_tracks_is_rejected(): void
    {
        $response = $this->from("/programs/{$this->program->slug}")
            ->post("/programs/{$this->program->slug}/register", $this->microForm([
                'participant_dob' => now()->subYears(25)->toDateString(),
            ]));

        $response->assertSessionHasErrors('participant_dob');
        $this->assertDatabaseCount('program_registrations', 0);
    }

    public function test_duplicate_submission_resumes_existing_registration(): void
    {
        $this->post("/programs/{$this->program->slug}/register", $this->microForm());
        $this->post("/programs/{$this->program->slug}/register", $this->microForm());

        $this->assertDatabaseCount('program_registrations', 1);
    }

    public function test_verification_by_token_and_otp(): void
    {
        $service = app(ProgramRegistrationService::class);

        $registration = $service->start($this->edition, $this->microForm());
        $this->assertTrue($service->verifyByToken($registration->fresh(), $registration->fresh()->email_verification_token));
        $this->assertEquals(ProgramRegistrationStatus::EmailVerified, $registration->fresh()->status);

        $second = $service->start($this->edition, $this->microForm([
            'guardian_email' => 'other@example.com',
            'participant_name' => 'Other Kid',
        ]));
        $otp = $service->sendVerification($second);
        $this->assertFalse($service->verifyByOtp($second->fresh(), '000000'));
        $this->assertTrue($service->verifyByOtp($second->fresh(), $otp));
    }

    public function test_begin_checkout_places_seat_hold_and_creates_order_with_metadata(): void
    {
        $service = app(ProgramRegistrationService::class);
        $registration = ProgramRegistration::factory()->verified()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        $order = $service->beginCheckout($registration);

        $registration->refresh();
        $this->assertEquals(ProgramRegistrationStatus::PaymentPending, $registration->status);
        $this->assertNotNull($registration->seat_held_until);
        $this->assertTrue($registration->seat_held_until->isFuture());
        $this->assertEquals($registration->uuid, data_get($order->metadata, 'program_registration_uuid'));
        $this->assertEquals($order->id, $registration->order_id);
    }

    public function test_checkout_requires_verified_email(): void
    {
        $registration = ProgramRegistration::factory()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        $this->expectException(ValidationException::class);
        app(ProgramRegistrationService::class)->beginCheckout($registration);
    }

    public function test_full_track_waitlists_new_checkout_instead_of_overselling(): void
    {
        ProgramRegistration::factory()->paid()->count(2)->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        $late = ProgramRegistration::factory()->verified()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        try {
            app(ProgramRegistrationService::class)->beginCheckout($late);
            $this->fail('Expected checkout to be blocked.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertEquals(ProgramRegistrationStatus::Waitlisted, $late->fresh()->status);
        $this->assertEquals(0, $this->juniorTrack->seatsRemaining());
    }

    public function test_active_seat_holds_count_toward_capacity(): void
    {
        ProgramRegistration::factory()->paid()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);
        ProgramRegistration::factory()->verified()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
            'seat_held_until' => now()->addMinutes(30),
        ]);

        $this->assertTrue($this->juniorTrack->isFull());

        // Expired holds release the seat.
        ProgramRegistration::query()->whereNotNull('seat_held_until')
            ->update(['seat_held_until' => now()->subMinute()]);

        $this->assertFalse($this->juniorTrack->isFull());
    }

    public function test_order_paid_transition_marks_registration_paid_and_sends_onboarding_email(): void
    {
        $service = app(ProgramRegistrationService::class);
        $registration = ProgramRegistration::factory()->verified()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        $order = $service->beginCheckout($registration);

        $order->refresh()->update([
            'payment_status' => PaymentStatus::Paid,
            'amount_paid' => $order->total,
            'balance_due' => 0,
            'paid_at' => now(),
        ]);

        $registration->refresh();
        $this->assertEquals(ProgramRegistrationStatus::Paid, $registration->status);
        $this->assertNull($registration->seat_held_until);
    }

    public function test_profile_completion_and_certificate_gate(): void
    {
        $registration = ProgramRegistration::factory()->paid()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        $updated = app(ProgramRegistrationService::class)->completeProfile($registration, [
            'emergency_contact_name' => 'Uncle Ben',
            'emergency_contact_phone' => '+2348022222222',
            'authorized_pickups' => [['name' => 'Uncle Ben', 'phone' => '+2348022222222']],
            'first_aid_consent' => true,
            'media_consent' => false,
            'custom_fields' => ['tshirt_size' => 'S'],
        ]);

        $this->assertNotNull($updated->profile_completed_at);
        $this->assertEquals(ProgramRegistrationStatus::ProfileCompleted, $updated->status);
        $this->assertFalse($updated->isCertificateEligible());

        $updated->update(['status' => ProgramRegistrationStatus::Completed]);
        $this->assertTrue($updated->fresh()->isCertificateEligible());
    }

    public function test_incomplete_required_custom_fields_block_profile_completion(): void
    {
        $registration = ProgramRegistration::factory()->paid()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        $updated = app(ProgramRegistrationService::class)->completeProfile($registration, [
            'emergency_contact_name' => 'Uncle Ben',
            'emergency_contact_phone' => '+2348022222222',
            'authorized_pickups' => [['name' => 'Uncle Ben', 'phone' => '+2348022222222']],
            'first_aid_consent' => true,
            'custom_fields' => [], // tshirt_size is required by the edition factory
        ]);

        $this->assertNull($updated->profile_completed_at);
        $this->assertEquals(ProgramRegistrationStatus::Paid, $updated->status);
    }

    public function test_offline_payment_confirms_seat_and_creates_paid_order(): void
    {
        $registration = ProgramRegistration::factory()->verified()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
        ]);

        $order = app(ProgramRegistrationService::class)->recordOfflinePayment($registration, [
            'amount' => 100000,
            'reference' => 'TRF-12345',
            'channel' => 'bank_transfer',
        ]);

        $registration->refresh();
        $this->assertEquals(ProgramRegistrationStatus::Paid, $registration->status);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertDatabaseHas('receipts', ['order_id' => $order->id]);
    }

    public function test_landing_page_renders_current_edition(): void
    {
        $this->get('/programs/summer-ai')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/Show')
                ->where('edition.title', $this->edition->title)
                ->has('tracks', 2));
    }

    public function test_summer_ai_short_link_redirects(): void
    {
        $this->get('/summer-ai')->assertRedirect('/programs/summer-ai');
    }

    public function test_resend_bounce_webhook_flags_email_invalid(): void
    {
        $registration = ProgramRegistration::factory()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->juniorTrack->id,
            'guardian_email' => 'bounce@example.com',
        ]);

        $this->postJson('/webhooks/resend', [
            'type' => 'email.bounced',
            'data' => ['to' => ['bounce@example.com']],
        ])->assertOk();

        $this->assertNotNull($registration->fresh()->email_invalid_at);
    }
}
