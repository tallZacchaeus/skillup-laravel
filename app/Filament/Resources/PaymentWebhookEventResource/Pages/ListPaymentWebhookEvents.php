<?php

namespace App\Filament\Resources\PaymentWebhookEventResource\Pages;

use App\Filament\Resources\PaymentWebhookEventResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentWebhookEvents extends ListRecords
{
    protected static string $resource = PaymentWebhookEventResource::class;
}
