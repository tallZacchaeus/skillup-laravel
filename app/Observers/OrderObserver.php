<?php

namespace App\Observers;

use App\Enums\PaymentStatus;
use App\Models\Catalog\Order;
use App\Services\Programs\ProgramRegistrationService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('payment_status') || ! data_get($order->metadata, 'program_registration_uuid')) {
            return;
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            app(ProgramRegistrationService::class)->handleOrderPaid($order);
        }

        if (in_array($order->payment_status, [PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded], true)) {
            app(ProgramRegistrationService::class)->handleOrderRefunded($order);
        }
    }
}
