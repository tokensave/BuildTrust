<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Thread;

use App\DTO\Ad\ShowAdData;
use App\Models\Thread;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class GetThreadData extends Data
{
    public function __construct(
        public int $id,
        #[DataCollectionOf(ParticipantData::class)]
        public DataCollection $participants,
        public ?LatestMessageData $latest_message,
        public ?ShowAdData $ad,
    ) {}

    public static function fromModel(Thread $thread): self
    {
        return new self(
            id: $thread->id,
            participants: ParticipantData::collect($thread->participants, DataCollection::class),
            latest_message: $thread->latestMessage
                ? LatestMessageData::from($thread->latestMessage)
                : null,
            ad: $thread->ad
                ? ShowAdData::fromModel($thread->ad)
                : null,
        );
    }
}
