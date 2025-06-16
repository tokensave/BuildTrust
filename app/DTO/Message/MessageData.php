<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Message;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class MessageData extends Data
{
    public function __construct(
        public string $content,
        public int $ad_id,
        public int $sender_id,
        public int $receiver_id,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return self::from([
                ...$request->validated(),
            ]
        );
    }
}
