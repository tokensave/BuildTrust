<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Message;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class StoreMessageData extends Data
{
    public function __construct(
        public string $content,
        public int $author_id,
    ){}

    public static function fromRequest(Request $request): self
    {
        return self::from([
            'content' => $request->input('content'),
            'author_id' => auth()->id(),
        ]);
    }
}
