<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Message;

use App\Models\Message;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class MessageData extends Data
{
    public function __construct(
        public string $content,
        public int $author_id,
        #[CastWith(DateTimeInterfaceCast::class)]
        public string $created_at,
        public ?string $author_name
    ) {
    }

   public static function fromModel(Message $message): self
   {
       return new self(
           content: $message->content,
           author_id: $message->author_id,
           created_at: $message->created_at->toDateTimeString(),
           author_name: $message->author->username
       );
   }
}
