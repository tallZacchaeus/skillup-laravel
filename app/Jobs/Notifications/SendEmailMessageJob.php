<?php

namespace App\Jobs\Notifications;

use App\Models\Notifications\EmailMessage;
use App\Services\Notifications\EmailDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public EmailMessage $emailMessage
    ) {}

    public function handle(EmailDeliveryService $service): void
    {
        $service->send($this->emailMessage);
    }
}
