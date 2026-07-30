<?php

namespace App\Enums;

enum DiscountRedemptionStatus: string
{
    case Locked = 'locked';
    case Redeemed = 'redeemed';
    case Released = 'released';
    case Cancelled = 'cancelled';
}
