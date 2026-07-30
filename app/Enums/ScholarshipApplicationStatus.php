<?php

namespace App\Enums;

enum ScholarshipApplicationStatus: string
{
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
