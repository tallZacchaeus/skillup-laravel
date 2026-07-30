<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\WebhookEventStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Catalog\PaymentWebhookEvent;
use App\Models\Catalog\Product;
use App\Models\Content\Lead;
use App\Models\Support\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Local/demo data so the admin dashboard has something to display.
 * Never run in production — guarded in run().
 */
class DemoDashboardSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoDashboardSeeder is for local/demo environments only.');

            return;
        }

        $products = Product::query()->pluck('id');
        $cohorts = Cohort::query()->pluck('id');

        // Paid orders spread over the last 30 days, plus a few pending ones.
        Order::factory()
            ->count(22)
            ->state(function () {
                $paidAt = now()->subDays(random_int(0, 29))->setTime(random_int(8, 20), random_int(0, 59));
                $total = collect([100000, 150000, 200000])->random();

                return [
                    'status' => OrderStatus::Paid,
                    'payment_status' => PaymentStatus::Paid,
                    'total' => $total,
                    'subtotal' => $total,
                    'amount_paid' => $total,
                    'balance_due' => 0,
                    'paid_at' => $paidAt,
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ];
            })
            ->create();

        Order::factory()->count(4)->create();

        // Enrollments across the last 8 weeks in a mix of states.
        foreach ([
            [EnrollmentStatus::Active, 18],
            [EnrollmentStatus::Pending, 3],
            [EnrollmentStatus::Failed, 2],
            [EnrollmentStatus::Partial, 1],
        ] as [$status, $count]) {
            Enrollment::factory()
                ->count($count)
                ->state(function () use ($status, $products, $cohorts) {
                    $createdAt = now()->subDays(random_int(0, 55));

                    return [
                        'status' => $status,
                        'product_id' => $products->isNotEmpty() ? $products->random() : null,
                        'cohort_id' => $cohorts->isNotEmpty() ? $cohorts->random() : null,
                        'failed_reason' => $status === EnrollmentStatus::Failed ? 'Moodle API timeout' : null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                })
                ->create();
        }

        // Leads over the last two weeks.
        foreach (range(1, 9) as $i) {
            Lead::create([
                'email' => "demo-lead-{$i}@example.com",
                'name' => "Demo Lead {$i}",
                'type' => collect(['newsletter', 'download', 'contact'])->random(),
                'created_at' => now()->subDays(random_int(0, 13)),
            ]);
        }

        // One failed webhook and an open support ticket for the attention row.
        PaymentWebhookEvent::create([
            'provider' => 'paystack',
            'event' => 'charge.success',
            'event_key' => 'demo-'.Str::uuid(),
            'reference' => 'demo-ref-'.Str::random(8),
            'payload_hash' => Str::random(40),
            'status' => WebhookEventStatus::Failed,
            'payload' => ['demo' => true],
            'error' => 'Signature mismatch (demo data)',
        ]);

        SupportTicket::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => User::query()->value('id'),
            'requester_name' => 'Demo Learner',
            'requester_email' => 'demo-learner@example.com',
            'subject' => 'Cannot access course materials',
            'category' => 'access',
            'priority' => 'normal',
            'status' => 'open',
            'source' => 'web',
            'last_activity_at' => now(),
        ]);
    }
}
