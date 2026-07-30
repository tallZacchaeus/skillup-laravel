<?php

namespace App\Enums;

enum ProgramEditionStatus: string
{
    case Draft = 'draft';
    case Announced = 'announced';
    case RegistrationOpen = 'registration_open';
    case SoldOut = 'sold_out';
    case Running = 'running';
    case Completed = 'completed';
    case Archived = 'archived';

    /**
     * Statuses whose landing page is publicly visible.
     *
     * @return array<int, string>
     */
    public static function publicValues(): array
    {
        return [
            self::Announced->value,
            self::RegistrationOpen->value,
            self::SoldOut->value,
            self::Running->value,
            self::Completed->value,
            self::Archived->value,
        ];
    }

    public function acceptsRegistrations(): bool
    {
        return $this === self::RegistrationOpen;
    }
}
