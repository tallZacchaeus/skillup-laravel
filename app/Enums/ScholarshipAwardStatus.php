<?php

namespace App\Enums;

enum ScholarshipAwardStatus: string
{
    case Active = 'active';
    case Redeemed = 'redeemed';
    case Revoked = 'revoked';
    case Expired = 'expired';
}
