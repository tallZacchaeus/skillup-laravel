<?php

namespace App\Enums;

enum ProgramRegistrationStatus: string
{
    case Started = 'started';
    case EmailVerified = 'email_verified';
    case PaymentPending = 'payment_pending';
    case Paid = 'paid';
    case ProfileCompleted = 'profile_completed';
    case Enrolled = 'enrolled';
    case Completed = 'completed';
    case Waitlisted = 'waitlisted';
    case Cancelled = 'cancelled';
    case Abandoned = 'abandoned';

    /**
     * Statuses that consume a confirmed seat.
     *
     * @return array<int, string>
     */
    public static function seatConsumingValues(): array
    {
        return [
            self::Paid->value,
            self::ProfileCompleted->value,
            self::Enrolled->value,
            self::Completed->value,
        ];
    }

    public function isPaidOrBeyond(): bool
    {
        return in_array($this->value, self::seatConsumingValues(), true);
    }
}
