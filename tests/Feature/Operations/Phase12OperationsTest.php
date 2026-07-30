<?php

namespace Tests\Feature\Operations;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Catalog\OrderItem;
use App\Models\Catalog\Payment;
use App\Models\Catalog\Product;
use App\Models\Lms\LmsSyncLog;
use App\Models\Notifications\EmailMessage;
use App\Models\Notifications\WhatsappMessage;
use App\Models\Operations\ExportRequest;
use App\Models\Operations\FormSubmission;
use App\Models\Operations\OperationalHealthCheck;
use App\Models\Support\SupportTicket;
use App\Models\User;
use App\Services\Operations\ExportService;
use App\Services\Operations\OperationalHealthService;
use App\Services\Operations\OperationalReportService;
use Database\Seeders\ProductCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase12OperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_creates_form_submission_inbox_record(): void
    {
        Queue::fake();

        $this->postJson(route('leads.contact'), [
            'name' => 'Support Prospect',
            'email' => 'prospect@example.com',
            'phone' => '08000000000',
            'message' => 'I need guidance.',
        ])->assertOk();

        $this->assertDatabaseHas('form_submissions', [
            'form_key' => 'contact_page',
            'email' => 'prospect@example.com',
            'status' => 'new',
        ]);
    }

    public function test_resource_download_creates_form_submission_record(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('resources-files/test.pdf', 'dummy content');

        $category = \App\Models\Content\ResourceCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $downloadable = \App\Models\Content\Downloadable::create([
            'resource_category_id' => $category->id,
            'title' => 'Ops Guide',
            'slug' => 'ops-guide',
            'description' => 'Operational guide.',
            'file_path' => 'resources-files/test.pdf',
            'status' => 'published',
        ]);

        $this->post(route('resources.download', ['slug' => $downloadable->slug]), [
            'name' => 'Downloader',
            'email' => 'downloader@example.com',
        ])->assertOk();

        $this->assertDatabaseHas('form_submissions', [
            'form_key' => 'downloadable_resource',
            'email' => 'downloader@example.com',
            'subject' => 'Resource download: Ops Guide',
        ]);
    }

    public function test_support_ticket_and_message_persist_for_admin_management(): void
    {
        $user = User::factory()->create();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'requester_email' => $user->email,
            'subject' => 'Cannot access Moodle',
            'category' => 'lms',
            'priority' => 'urgent',
            'status' => 'open',
            'source' => 'learner',
        ]);

        $ticket->messages()->create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'author_email' => $user->email,
            'body' => 'My course is missing.',
        ]);

        $this->assertDatabaseHas('support_tickets', [
            'subject' => 'Cannot access Moodle',
            'priority' => 'urgent',
        ]);
        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'body' => 'My course is missing.',
        ]);
        $this->assertTrue(Route::has('filament.admin.resources.support-tickets.index'));
    }

    public function test_operational_report_matches_available_data(): void
    {
        Queue::fake();
        $this->seed(ProductCatalogueSeeder::class);

        $user = User::factory()->create();
        $product = Product::firstOrFail();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'currency' => 'NGN',
            'subtotal' => 100000,
            'total' => 100000,
            'amount_paid' => 100000,
            'balance_due' => 0,
            'paid_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'quantity' => 2,
            'unit_amount' => 50000,
            'total' => 100000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'provider' => 'paystack',
            'status' => PaymentStatus::Paid,
            'currency' => 'NGN',
            'amount' => 100000,
            'paid_at' => now(),
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'status' => EnrollmentStatus::Active,
        ]);

        SupportTicket::create([
            'user_id' => $user->id,
            'requester_email' => $user->email,
            'subject' => 'Billing question',
            'category' => 'billing',
            'priority' => 'normal',
            'status' => 'open',
            'source' => 'learner',
        ]);

        EmailMessage::create([
            'recipient_email' => 'failed@example.com',
            'subject' => 'Failed message',
            'status' => 'failed',
        ]);

        $summary = app(OperationalReportService::class)->summary();

        $this->assertSame(1, $summary['revenue']['paid_orders']);
        $this->assertSame(100000.0, $summary['revenue']['gross_revenue']);
        $this->assertSame(1, $summary['enrollments']['active']);
        $this->assertSame(1, $summary['payments']['paid']);
        $this->assertSame(1, $summary['support']['open']);
        $this->assertSame(1, $summary['email']['failed']);
        $this->assertSame(2, $summary['product_demand'][0]['quantity']);
    }

    public function test_export_service_generates_payment_csv(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'currency' => 'NGN',
            'subtotal' => 25000,
            'total' => 25000,
            'amount_paid' => 25000,
            'paid_at' => now(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'provider' => 'paystack',
            'status' => PaymentStatus::Paid,
            'currency' => 'NGN',
            'amount' => 25000,
            'paid_at' => now(),
        ]);

        $request = ExportRequest::create([
            'user_id' => $user->id,
            'name' => 'Payments export',
            'export_type' => 'payments',
        ]);

        $generated = app(ExportService::class)->generate($request);

        $this->assertSame('completed', $generated->status);
        $this->assertSame(1, $generated->row_count);
        Storage::disk('local')->assertExists($generated->file_path);
    }

    public function test_operational_health_snapshot_records_failed_operations(): void
    {
        LmsSyncLog::create([
            'action' => 'enroll',
            'status' => 'failed',
            'error_message' => 'Moodle unavailable',
        ]);

        WhatsappMessage::create([
            'recipient_phone' => '+2348000000000',
            'template_name' => 'security_alert',
            'status' => 'failed',
        ]);

        $snapshot = app(OperationalHealthService::class)->snapshot();

        $this->assertSame('attention', $snapshot['failed_operations']['status']);
        $this->assertSame(1, $snapshot['failed_operations']['metrics']['moodle_sync_failures']);
        $this->assertSame(1, $snapshot['failed_operations']['metrics']['whatsapp_failures']);
        $this->assertDatabaseHas('operational_health_checks', [
            'name' => 'failed_operations',
            'status' => 'attention',
        ]);
        $this->assertSame(3, OperationalHealthCheck::count());
    }
}
