<?php

namespace Tests\Feature\Services\Notifications;

use App\Models\Notifications\EmailMessage;
use App\Models\Notifications\EmailDeliveryLog;
use App\Services\Notifications\EmailDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.resend.api_key' => 'resend-test-key']);
        config(['services.resend.from_address' => 'hello@example.com']);
        config(['services.resend.base_url' => 'https://api.resend.com']);
        config(['services.zeptomail.api_key' => 'test-key']);
        config(['services.zeptomail.from_address' => 'test@example.com']);
        config(['services.zeptomail.base_url' => 'https://api.zeptomail.com/v1.1']);
    }

    private function makeMessage(): EmailMessage
    {
        return EmailMessage::create([
            'recipient_email' => 'user@example.com',
            'subject' => 'Test Subject',
            'body_html' => '<p>Test Body</p>',
            'status' => 'pending',
        ]);
    }

    public function test_it_sends_email_via_resend_as_primary()
    {
        Http::fake([
            'https://api.resend.com/emails' => Http::response(['id' => 'resend-123'], 200),
        ]);

        $message = $this->makeMessage();

        $result = (new EmailDeliveryService())->send($message);

        $this->assertTrue($result);
        $this->assertEquals('sent', $message->fresh()->status);

        $log = EmailDeliveryLog::where('email_message_id', $message->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('resend', $log->provider);
        $this->assertEquals('success', $log->status);
        $this->assertEquals('resend-123', $log->provider_message_id);
    }

    public function test_it_falls_back_to_zeptomail_when_resend_fails()
    {
        Http::fake([
            'https://api.resend.com/emails' => Http::response(['error' => 'boom'], 500),
            'https://api.zeptomail.com/v1.1/email' => Http::response(['data' => [['message_id' => '12345']]], 200),
        ]);

        $message = $this->makeMessage();

        $result = (new EmailDeliveryService())->send($message);

        $this->assertTrue($result);
        $this->assertEquals('fallback_sent', $message->fresh()->status);

        $logs = EmailDeliveryLog::where('email_message_id', $message->id)->orderBy('attempt_number')->get();
        $this->assertCount(2, $logs);
        $this->assertEquals('resend', $logs[0]->provider);
        $this->assertEquals('failed', $logs[0]->status);
        $this->assertEquals('zeptomail', $logs[1]->provider);
        $this->assertEquals('success', $logs[1]->status);
    }

    public function test_it_falls_back_to_ses_when_resend_and_zeptomail_fail()
    {
        Http::fake([
            'https://api.resend.com/emails' => Http::response(['error' => 'boom'], 500),
            'https://api.zeptomail.com/v1.1/email' => Http::response(['error' => 'API Error'], 500),
        ]);

        Mail::fake();

        $message = $this->makeMessage();

        $result = (new EmailDeliveryService())->send($message);

        $this->assertTrue($result);
        $this->assertEquals('fallback_sent', $message->fresh()->status);

        $logs = EmailDeliveryLog::where('email_message_id', $message->id)->orderBy('attempt_number')->get();
        $this->assertCount(3, $logs);
        $this->assertEquals(['resend', 'zeptomail', 'ses'], $logs->pluck('provider')->all());
        $this->assertEquals(['failed', 'failed', 'success'], $logs->pluck('status')->all());
    }

    public function test_it_skips_resend_when_unconfigured_and_uses_zeptomail()
    {
        config(['services.resend.api_key' => null]);

        Http::fake([
            'https://api.zeptomail.com/v1.1/email' => Http::response(['data' => [['message_id' => '12345']]], 200),
        ]);

        $message = $this->makeMessage();

        $result = (new EmailDeliveryService())->send($message);

        $this->assertTrue($result);
        $this->assertEquals('fallback_sent', $message->fresh()->status);

        $log = EmailDeliveryLog::where('email_message_id', $message->id)->first();
        $this->assertEquals('zeptomail', $log->provider);
        $this->assertEquals('success', $log->status);
    }

    public function test_ses_mailer_can_be_resolved()
    {
        $mailer = app('mail.manager')->mailer('ses');
        $this->assertNotNull($mailer);
    }
}
