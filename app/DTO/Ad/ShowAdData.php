<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Ad;

use App\DTO\User\UserData;
use App\Models\Ad;
use Spatie\LaravelData\Data;

class ShowAdData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public ?string $price,
        public string $status,
        public string $image_url,
        public array $media,
        public string $created_at,
        public UserData $user
    ) {}

    public static function fromModel(Ad $ad): self
    {
        return new self(
            id: $ad->id,
            title: $ad->title,
            description: $ad->description,
            price: $ad->price,
            status: $ad->status->value,
            image_url: $ad->getFirstMediaUrl('images') ?: asset('images/default-ad.png'),
            media: $ad->getMedia('images')->map(fn ($m) => [
                'original_url'  => $m->getUrl(),
            ])->toArray(),
            created_at: $ad->created_at->toDateTimeString(),
            user: UserData::fromModel($ad->user),
        );
    }
}
