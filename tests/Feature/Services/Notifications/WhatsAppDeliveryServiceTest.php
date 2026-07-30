<?php

namespace Tests\Feature\Services\Notifications;

use App\Models\Notifications\WhatsappMessage;
use App\Models\Notifications\WhatsappDeliveryLog;
use App\Services\Notifications\WhatsAppDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.whatsapp.phone_number_id' => '123456789']);
        config(['services.whatsapp.access_token' => 'test-token']);
        config(['services.whatsapp.api_version' => 'v20.0']);
        config(['services.whatsapp.base_url' => 'https://graph.facebook.com']);
    }

    public function test_it_sends_whatsapp_message_successfully()
    {
        Http::fake([
            'https://graph.facebook.com/v20.0/123456789/messages' => Http::response([
                'messages' => [['id' => 'wamid.12345']]
            ], 200)
        ]);

        $message = WhatsappMessage::create([
            'recipient_phone' => '+1234567890',
            'template_name' => 'hello_world',
            'status' => 'pending',
        ]);

        $service = new WhatsAppDeliveryService();
        $result = $service->send($message);

        $this->assertTrue($result);
        $this->assertEquals('sent', $message->fresh()->status);

        $log = WhatsappDeliveryLog::where('whatsapp_message_id', $message->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('success', $log->status);
        $this->assertEquals('wamid.12345', $log->provider_message_id);
    }

    public function test_it_logs_failure_when_whatsapp_api_fails()
    {
        Http::fake([
            'https://graph.facebook.com/v20.0/123456789/messages' => Http::response([
                'error' => ['message' => 'Invalid token']
            ], 401)
        ]);

        $message = WhatsappMessage::create([
            'recipient_phone' => '+1234567890',
            'template_name' => 'hello_world',
            'status' => 'pending',
        ]);

        $service = new WhatsAppDeliveryService();
        $result = $service->send($message);

        $this->assertFalse($result);
        $this->assertEquals('failed', $message->fresh()->status);

        $log = WhatsappDeliveryLog::where('whatsapp_message_id', $message->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('failed', $log->status);
        $this->assertStringContainsString('Invalid token', $log->error_message);
    }
}
