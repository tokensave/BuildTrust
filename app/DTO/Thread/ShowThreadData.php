<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Thread;

use App\DTO\Ad\ShowAdData;
use App\DTO\Message\MessageData;
use App\Models\Thread;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ShowThreadData extends Data
{
    public function __construct(
        public int $id,
        #[DataCollectionOf(ParticipantData::class)]
        public DataCollection $participants,
        #[DataCollectionOf(MessageData::class)]
        public DataCollection $messages,
        public ?ShowAdData $ad,
    ) {}

    public static function fromModel(Thread $thread): self
    {
        return new self(
            id: $thread->id,
            participants: ParticipantData::collect($thread->participants, DataCollection::class),
            messages: MessageData::collect($thread->messages, DataCollection::class),
            ad: $thread->ad ? ShowAdData::fromModel($thread->ad) : null,
        );
    }
}
