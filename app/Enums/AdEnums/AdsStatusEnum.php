<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Enums\AdEnums;

enum AdsStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

}
