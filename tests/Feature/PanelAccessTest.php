<?php

namespace Tests\Feature;

use App\Filament\Corporate\Resources\CorporateEnrollmentResource;
use App\Filament\Instructor\Resources\AssignedCohortResource;
use App\Filament\Learner\Resources\LearnerEnrollmentResource;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CorporateAccount;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\InstructorProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_panel_access_is_gated_by_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $learner = User::factory()->create();
        $learner->assignRole('Learner');

        $corporate = User::factory()->create();
        $corporate->assignRole('Corporate');

        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->assertTrue($learner->canAccessPanel(Filament::getPanel('learner')));
        $this->assertFalse($learner->canAccessPanel(Filament::getPanel('corporate')));
        $this->assertFalse($learner->canAccessPanel(Filament::getPanel('instructor')));
        $this->assertFalse($learner->canAccessPanel(Filament::getPanel('admin')));

        $this->assertTrue($corporate->canAccessPanel(Filament::getPanel('corporate')));
        $this->assertFalse($corporate->canAccessPanel(Filament::getPanel('learner')));

        $this->assertTrue($instructor->canAccessPanel(Filament::getPanel('instructor')));
        $this->assertFalse($instructor->canAccessPanel(Filament::getPanel('corporate')));

        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('learner')));
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('corporate')));
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('instructor')));
    }

    public function test_learner_enrollment_resource_only_returns_current_user_courses(): void
    {
        $learner = User::factory()->create();
        $otherLearner = User::factory()->create();

        $ownEnrollment = Enrollment::factory()->create(['user_id' => $learner->id]);
        $otherEnrollment = Enrollment::factory()->create(['user_id' => $otherLearner->id]);

        $this->actingAs($learner);

        $ids = LearnerEnrollmentResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($ownEnrollment->id, $ids);
        $this->assertNotContains($otherEnrollment->id, $ids);
    }

    public function test_corporate_resources_only_return_current_company_records(): void
    {
        $contact = User::factory()->create();
        $otherContact = User::factory()->create();
        $company = CorporateAccount::factory()->create(['primary_contact_user_id' => $contact->id]);
        $otherCompany = CorporateAccount::factory()->create(['primary_contact_user_id' => $otherContact->id]);

        $ownEnrollment = Enrollment::factory()->create(['corporate_account_id' => $company->id]);
        $otherEnrollment = Enrollment::factory()->create(['corporate_account_id' => $otherCompany->id]);

        $this->actingAs($contact);

        $ids = CorporateEnrollmentResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($ownEnrollment->id, $ids);
        $this->assertNotContains($otherEnrollment->id, $ids);
    }

    public function test_instructor_resources_only_return_assigned_cohorts(): void
    {
        $instructor = User::factory()->create();
        $otherInstructor = User::factory()->create();
        $profile = InstructorProfile::factory()->create(['user_id' => $instructor->id]);
        $otherProfile = InstructorProfile::factory()->create(['user_id' => $otherInstructor->id]);

        $ownCohort = Cohort::factory()->create(['instructor_profile_id' => $profile->id]);
        $otherCohort = Cohort::factory()->create(['instructor_profile_id' => $otherProfile->id]);

        $this->actingAs($instructor);

        $ids = AssignedCohortResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($ownCohort->id, $ids);
        $this->assertNotContains($otherCohort->id, $ids);
    }
}
