<?php

namespace Tests\Feature\Services\Notifications;

use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
use App\Models\Notifications\EmailMessage;
use App\Models\Notifications\WhatsappMessage;
use App\Models\User;
use App\Notifications\OtpAuthNotification;
use App\Notifications\MoodleAccessSuccessNotification;
use App\Notifications\MoodleAccessFailedNotification;
use App\Notifications\OrderPaidNotification;
use App\Notifications\InstallmentReminderNotification;
use App\Models\Catalog\Installment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Hash;
use App\Jobs\Notifications\SendEmailMessageJob;
use App\Jobs\Notifications\SendWhatsappMessageJob;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\ProductCatalogueSeeder;
use Tests\TestCase;

class QueuedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        // Seed necessary events and catalogue products
        $this->seed(ProductCatalogueSeeder::class);
        $this->seed(NotificationSeeder::class);
    }

    private function createUser(): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $user->learnerProfile()->create([
            'phone' => '+1234567890',
        ]);

        return $user;
    }

    public function test_otp_notification_dispatches_email_and_whatsapp_jobs()
    {
        $user = $this->createUser();

        $user->notify(new OtpAuthNotification('123456'));

        $emailMessage = EmailMessage::where('recipient_email', 'user@example.com')->first();
        $whatsappMessage = WhatsappMessage::where('recipient_phone', '+1234567890')->first();

        $this->assertNotNull($emailMessage, 'EmailMessage was not created');
        $this->assertNotNull($whatsappMessage, 'WhatsappMessage was not created');

        $this->assertEquals('queued', $emailMessage->status);
        $this->assertEquals('queued', $whatsappMessage->status);

        Queue::assertPushed(SendEmailMessageJob::class, function ($job) use ($emailMessage) {
            return $job->emailMessage->id === $emailMessage->id;
        });

        Queue::assertPushed(SendWhatsappMessageJob::class, function ($job) use ($whatsappMessage) {
            return $job->whatsappMessage->id === $whatsappMessage->id;
        });
    }

    public function test_moodle_access_success_notification_dispatches_email_job()
    {
        $user = $this->createUser();
        $product = Product::first();

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);

        $user->notify(new MoodleAccessSuccessNotification($enrollment));

        $emailMessage = EmailMessage::where('recipient_email', 'user@example.com')->first();
        $this->assertNotNull($emailMessage);

        Queue::assertPushed(SendEmailMessageJob::class);
    }

    public function test_payment_failed_notification_dispatches_email_and_whatsapp_jobs()
    {
        $user = $this->createUser();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 200000,
            'balance_due' => 200000,
        ]);
        $payment = \App\Models\Catalog\Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => 200000,
            'currency' => 'NGN',
            'status' => 'failed',
            'reference' => 'failed-ref',
        ]);

        $user->notify(new \App\Notifications\PaymentFailedNotification($payment));

        $emailMessage = EmailMessage::where('recipient_email', 'user@example.com')->first();
        $whatsappMessage = WhatsappMessage::where('recipient_phone', '+1234567890')->first();

        $this->assertNotNull($emailMessage);
        $this->assertNotNull($whatsappMessage);

        Queue::assertPushed(SendEmailMessageJob::class);
        Queue::assertPushed(SendWhatsappMessageJob::class);
    }

    public function test_security_alert_notification_dispatches_email_and_whatsapp_jobs()
    {
        $user = $this->createUser();

        $user->notify(new \App\Notifications\SecurityAlertNotification());

        $emailMessage = EmailMessage::where('recipient_email', 'user@example.com')->first();
        $whatsappMessage = WhatsappMessage::where('recipient_phone', '+1234567890')->first();

        $this->assertNotNull($emailMessage);
        $this->assertNotNull($whatsappMessage);

        Queue::assertPushed(SendEmailMessageJob::class);
        Queue::assertPushed(SendWhatsappMessageJob::class);
    }

    public function test_moodle_access_failed_notification_dispatches_email_and_whatsapp_jobs()
    {
        $user = $this->createUser();
        $product = Product::first();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'failed',
        ]);

        $user->notify(new \App\Notifications\MoodleAccessFailedNotification($enrollment, 'API timeout'));

        $emailMessage = EmailMessage::where('recipient_email', 'user@example.com')->first();
        $whatsappMessage = WhatsappMessage::where('recipient_phone', '+1234567890')->first();

        $this->assertNotNull($emailMessage);
        $this->assertNotNull($whatsappMessage);

        Queue::assertPushed(SendEmailMessageJob::class);
        Queue::assertPushed(SendWhatsappMessageJob::class);
    }
}
