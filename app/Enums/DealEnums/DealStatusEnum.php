<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Enums\DealEnums;

enum DealStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case  REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
}
