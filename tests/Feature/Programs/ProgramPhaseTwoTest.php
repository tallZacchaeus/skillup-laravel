<?php

namespace Tests\Feature\Programs;

use App\Enums\PaymentStatus;
use App\Enums\ProgramEditionStatus;
use App\Enums\ProgramRegistrationStatus;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramAttendanceRecord;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use App\Models\Programs\ProgramRegistration;
use App\Services\Programs\ProgramCertificateService;
use App\Services\Programs\ProgramRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProgramPhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    private ProgramEdition $edition;

    private ProgramEditionTrack $track;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $program = Program::factory()->create(['slug' => 'summer-ai']);
        $this->edition = ProgramEdition::factory()->create(['program_id' => $program->id]);

        $product = Product::factory()->create();
        ProductPrice::factory()->create(['product_id' => $product->id, 'is_default' => true, 'is_active' => true]);

        $this->track = ProgramEditionTrack::factory()->create([
            'program_edition_id' => $this->edition->id,
            'product_id' => $product->id,
            'capacity' => 1,
        ]);
    }

    private function completedRegistration(array $overrides = []): ProgramRegistration
    {
        return ProgramRegistration::factory()->create(array_merge([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->track->id,
            'status' => ProgramRegistrationStatus::Completed,
            'email_verified_at' => now(),
            'profile_completed_at' => now(),
        ], $overrides));
    }

    public function test_certificate_issue_is_gated_and_idempotent(): void
    {
        $service = app(ProgramCertificateService::class);

        $ineligible = ProgramRegistration::factory()->paid()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->track->id,
        ]);

        try {
            $service->issue($ineligible);
            $this->fail('Expected the certificate gate to block issuance.');
        } catch (ValidationException) {
            // expected — not completed, profile incomplete
        }

        $eligible = $this->completedRegistration(['guardian_email' => 'cert@example.com']);
        $first = $service->issue($eligible);
        $second = $service->issue($eligible->fresh());

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('program_certificates', 1);
        $this->assertNotEmpty($first->serial);
    }

    public function test_certificate_verify_page_confirms_genuine_serials(): void
    {
        $certificate = app(ProgramCertificateService::class)->issue($this->completedRegistration());

        $this->get('/certificates/verify?serial='.$certificate->serial)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Certificates/Verify')
                ->where('certificate.serial', $certificate->serial)
                ->where('certificate.recipientName', $certificate->recipient_name));

        $this->get('/certificates/verify?serial=FAKE-SERIAL-123')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('certificate', null)->where('checked', true));

        $this->get('/certificates/'.$certificate->serial)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Public/Certificates/Show'));
    }

    public function test_refund_releases_seat_and_promotes_waitlist_with_offer(): void
    {
        $service = app(ProgramRegistrationService::class);

        $paid = ProgramRegistration::factory()->verified()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->track->id,
        ]);
        $order = $service->beginCheckout($paid);
        $order->refresh()->update(['payment_status' => PaymentStatus::Paid, 'amount_paid' => $order->total, 'balance_due' => 0]);
        $this->assertEquals(ProgramRegistrationStatus::Paid, $paid->fresh()->status);

        $waitlisted = ProgramRegistration::factory()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->track->id,
            'status' => ProgramRegistrationStatus::Waitlisted,
            'email_verified_at' => now(),
        ]);

        $order->refresh()->update(['payment_status' => PaymentStatus::Refunded]);

        $this->assertEquals(ProgramRegistrationStatus::Cancelled, $paid->fresh()->status);

        $promoted = $waitlisted->fresh();
        $this->assertEquals(ProgramRegistrationStatus::EmailVerified, $promoted->status);
        $this->assertNotNull(data_get($promoted->metadata, 'waitlist_offer_expires_at'));
    }

    public function test_lapsed_waitlist_offer_returns_to_waitlist_and_promotes_next(): void
    {
        $service = app(ProgramRegistrationService::class);

        $first = ProgramRegistration::factory()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->track->id,
            'status' => ProgramRegistrationStatus::EmailVerified,
            'email_verified_at' => now(),
            'metadata' => ['waitlist_offer_expires_at' => now()->subHour()->toIso8601String()],
        ]);
        $second = ProgramRegistration::factory()->create([
            'program_edition_id' => $this->edition->id,
            'program_edition_track_id' => $this->track->id,
            'status' => ProgramRegistrationStatus::Waitlisted,
            'email_verified_at' => now(),
        ]);

        $lapsed = $service->expireWaitlistOffers();

        $this->assertEquals(1, $lapsed);
        $this->assertEquals(ProgramRegistrationStatus::Waitlisted, $first->fresh()->status);
        $this->assertNotNull(data_get($first->fresh()->metadata, 'waitlist_offer_lapsed_at'));

        $promoted = $second->fresh();
        $this->assertEquals(ProgramRegistrationStatus::EmailVerified, $promoted->status);
        $this->assertNotNull(data_get($promoted->metadata, 'waitlist_offer_expires_at'));
    }

    public function test_attendance_records_are_unique_per_day(): void
    {
        $registration = $this->completedRegistration();

        ProgramAttendanceRecord::firstOrCreate(
            ['program_registration_id' => $registration->id, 'attended_on' => \Illuminate\Support\Carbon::parse('2026-08-03')],
            ['present' => true],
        );
        ProgramAttendanceRecord::firstOrCreate(
            ['program_registration_id' => $registration->id, 'attended_on' => \Illuminate\Support\Carbon::parse('2026-08-03')],
            ['present' => true],
        );
        ProgramAttendanceRecord::firstOrCreate(
            ['program_registration_id' => $registration->id, 'attended_on' => \Illuminate\Support\Carbon::parse('2026-08-04')],
            ['present' => true],
        );

        $this->assertEquals(2, $registration->attendanceDays());
    }

    public function test_safeguarding_purge_respects_retention_window(): void
    {
        $this->edition->update([
            'status' => ProgramEditionStatus::Completed,
            'ends_on' => now()->subMonths(7)->toDateString(),
            'safeguarding_retention_months' => 6,
        ]);

        $registration = $this->completedRegistration([
            'medical_notes' => 'Peanut allergy',
            'authorized_pickups' => [['name' => 'Uncle Ben', 'phone' => '+2348022222222']],
        ]);

        $this->artisan('programs:purge-safeguarding-data')->assertSuccessful();

        $fresh = $registration->fresh();
        $this->assertNull($fresh->medical_notes);
        $this->assertNull($fresh->authorized_pickups);
        $this->assertNotNull($fresh->safeguarding_purged_at);

        // A recent edition is untouched.
        $this->edition->update(['ends_on' => now()->subMonth()->toDateString()]);
        $recent = $this->completedRegistration([
            'guardian_email' => 'recent@example.com',
            'participant_name' => 'Recent Kid',
            'medical_notes' => 'Asthma',
        ]);

        $this->artisan('programs:purge-safeguarding-data')->assertSuccessful();
        $this->assertNotNull($recent->fresh()->medical_notes);
    }
}
