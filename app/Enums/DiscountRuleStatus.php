<?php

namespace App\Enums;

enum DiscountRuleStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case Expired = 'expired';
}
