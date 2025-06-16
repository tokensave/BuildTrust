<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Thread;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class StoreThreadData extends Data
{
    public function __construct(
        public int $ad_id,
        public int $recipient_id,
        public int $author_id,
    ){}

    public static function fromRequest(Request $request): self
    {
        return self::from([
            ...$request->only(['ad_id', 'recipient_id', 'author_id']),
        ]);
    }
}
