<?php

namespace App\Jobs\Notifications;

use App\Models\Notifications\WhatsappMessage;
use App\Services\Notifications\WhatsAppDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public WhatsappMessage $whatsappMessage,
        public array $templateParams = [],
        public string $languageCode = 'en_US'
    ) {}

    public function handle(WhatsAppDeliveryService $service): void
    {
        $service->send($this->whatsappMessage, $this->templateParams, $this->languageCode);
    }
}
