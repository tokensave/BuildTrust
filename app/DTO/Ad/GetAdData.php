<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Ad;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
use App\Models\Ad;
use Spatie\LaravelData\Data;

class GetAdData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public AdTypeEnum $type,
        public ?AdCategoryEnum $category,
        public ?AdSubcategoryEnum $subcategory,
        public ?string $location,
        public string $description,
        public ?string $price,
        public bool $is_urgent,
        public ?array $features,
        public string $status,
        public string $slug,
        public string $image_url,
        public array $media,
        public string $created_at,
    ) {}

    public static function fromModel(Ad $ad): self
    {
        return new self(
            id: $ad->id,
            title: $ad->title,
            type: $ad->type,
            category: $ad->category,
            subcategory: $ad->subcategory,
            location: $ad->location,
            description: $ad->description,
            price: $ad->price,
            is_urgent: $ad->is_urgent,
            features: $ad->features,
            status: $ad->status->value,
            slug: $ad->slug,
            image_url: $ad->getFirstMediaUrl('images') ?: asset('images/default-ad.png'),
            media: $ad->getMedia('images')->map(fn ($m) => [
                'id' => $m->id,
                'original_url'  => $m->getUrl(),
            ])->toArray(),
            created_at: $ad->created_at->toDateTimeString(),
        );
    }
}
