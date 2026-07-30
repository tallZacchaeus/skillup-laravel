<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\InstallmentStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CohortSession;
use App\Models\Catalog\CorporateAccount;
use App\Models\Catalog\CorporateLearner;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Installment;
use App\Models\Catalog\InstructorProfile;
use App\Models\Catalog\Order;
use App\Models\Catalog\PaymentPlan;
use App\Models\Catalog\Product;
use App\Models\Support\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Demo users and scoped data for the Learner, Instructor, and Corporate
 * portals so their dashboards have something to display in local/demo
 * environments. Never run in production — guarded in run().
 *
 * Logins (all with password "local-dev-password"):
 *   learner@demo.test / instructor@demo.test / corporate@demo.test
 */
class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('PortalDemoSeeder is for local/demo environments only.');

            return;
        }

        $products = Product::query()->pluck('id');
        $cohorts = Cohort::query()->get();

        $this->seedLearner($products, $cohorts);
        $this->seedInstructor($cohorts);
        $this->seedCorporate($products, $cohorts);
    }

    private function demoUser(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('local-dev-password')],
        );
        $user->assignRole($role);

        return $user;
    }

    private function seedLearner($products, $cohorts): void
    {
        $learner = $this->demoUser('learner@demo.test', 'Demo Learner', 'Learner');

        if (Enrollment::where('user_id', $learner->id)->exists()) {
            return;
        }

        foreach ([
            [EnrollmentStatus::Active, 0],
            [EnrollmentStatus::Active, 1],
            [EnrollmentStatus::Pending, 2],
        ] as [$status, $index]) {
            Enrollment::factory()->create([
                'user_id' => $learner->id,
                'product_id' => $products->get($index % max($products->count(), 1)),
                'cohort_id' => $cohorts->get($index % max($cohorts->count(), 1))?->id,
                'status' => $status,
            ]);
        }

        $order = Order::factory()->create(['user_id' => $learner->id]);
        $plan = PaymentPlan::factory()->create(['order_id' => $order->id]);
        Installment::factory()->create([
            'payment_plan_id' => $plan->id,
            'order_id' => $order->id,
            'installment_number' => 2,
            'status' => InstallmentStatus::Pending,
            'due_at' => now()->addDays(9),
        ]);

        SupportTicket::firstOrCreate(
            ['requester_email' => 'learner@demo.test'],
            [
                'uuid' => (string) Str::uuid(),
                'user_id' => $learner->id,
                'requester_name' => 'Demo Learner',
                'subject' => 'Question about my cohort schedule',
                'category' => 'general',
                'priority' => 'normal',
                'status' => 'open',
                'source' => 'web',
                'last_activity_at' => now(),
            ],
        );

        // Upcoming sessions on the learner's cohorts.
        $cohorts->take(2)->each(function (Cohort $cohort, int $i): void {
            if (CohortSession::where('cohort_id', $cohort->id)->exists()) {
                return;
            }

            CohortSession::factory()->count(2)->sequence(
                ['starts_at' => now()->addDays(2 + $i)->setTime(18, 0), 'ends_at' => now()->addDays(2 + $i)->setTime(20, 0)],
                ['starts_at' => now()->addDays(9 + $i)->setTime(18, 0), 'ends_at' => now()->addDays(9 + $i)->setTime(20, 0)],
            )->create(['cohort_id' => $cohort->id]);
        });
    }

    private function seedInstructor($cohorts): void
    {
        $instructor = $this->demoUser('instructor@demo.test', 'Demo Instructor', 'Instructor');

        $profile = InstructorProfile::firstOrCreate(
            ['user_id' => $instructor->id],
            ['title' => 'Senior Facilitator', 'bio' => 'Demo instructor profile.', 'skills' => ['Product', 'Mentorship']],
        );

        $cohorts->take(2)->each(function (Cohort $cohort) use ($profile): void {
            $cohort->update(['instructor_profile_id' => $profile->id]);
        });

        // A session inside the current week for the "Sessions this week" stat.
        $firstCohort = $cohorts->first();

        if ($firstCohort && ! CohortSession::where('cohort_id', $firstCohort->id)->whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()])->exists()) {
            CohortSession::factory()->create([
                'cohort_id' => $firstCohort->id,
                'title' => 'Weekly live class',
                'starts_at' => now()->endOfWeek()->subDay()->setTime(18, 0),
                'ends_at' => now()->endOfWeek()->subDay()->setTime(20, 0),
            ]);
        }
    }

    private function seedCorporate($products, $cohorts): void
    {
        $contact = $this->demoUser('corporate@demo.test', 'Demo Corporate Contact', 'Corporate');

        $account = CorporateAccount::firstOrCreate(
            ['primary_contact_user_id' => $contact->id],
            CorporateAccount::factory()->raw([
                'primary_contact_user_id' => $contact->id,
                'name' => 'Acme Technologies Ltd',
                'slug' => 'acme-technologies-'.random_int(100, 999),
            ]),
        );

        if (CorporateLearner::where('corporate_account_id', $account->id)->exists()) {
            return;
        }

        foreach ([['active', 4], ['invited', 2]] as [$status, $count]) {
            foreach (range(1, $count) as $i) {
                $member = User::factory()->create();
                $member->assignRole('Learner');

                CorporateLearner::create([
                    'corporate_account_id' => $account->id,
                    'user_id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'status' => $status,
                    'invited_at' => now()->subDays($i),
                    'accepted_at' => $status === 'active' ? now()->subDays($i - 1) : null,
                ]);

                if ($status === 'active') {
                    Enrollment::factory()->create([
                        'user_id' => $member->id,
                        'corporate_account_id' => $account->id,
                        'product_id' => $products->get($i % max($products->count(), 1)),
                        'cohort_id' => $cohorts->get($i % max($cohorts->count(), 1))?->id,
                        'status' => $i === 1 ? EnrollmentStatus::Pending : EnrollmentStatus::Active,
                    ]);
                }
            }
        }

        // An order with an outstanding balance for the account.
        Order::factory()->create([
            'user_id' => $contact->id,
            'corporate_account_id' => $account->id,
            'amount_paid' => 300000,
            'total' => 600000,
            'subtotal' => 600000,
            'balance_due' => 300000,
        ]);
    }
}
