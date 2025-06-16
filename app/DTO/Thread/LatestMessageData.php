<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Thread;

use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class LatestMessageData extends Data
{
    public function __construct(
        public string $content,
        #[CastWith(DateTimeInterfaceCast::class)]
        public string $created_at,
        public int $sender_id,
    ) {}
}
