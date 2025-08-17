<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Ad;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class UpdateAdData extends Data
{
    public function __construct(
        public string $title,
        public AdTypeEnum $type,
        public ?AdCategoryEnum $category,
        public ?AdSubcategoryEnum $subcategory,
        public ?string $location,
        public string $description,
        public ?float $price,
        public bool $is_urgent,
        public ?array $features,
        public AdsStatusEnum $status,
        /** @var UploadedFile[]|null */
        public ?array $newImages = null,
        /** @var array<int>|null Удаляемые media IDs */
        public ?array $deletedMediaIds = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return self::from([
            ...$request->validated(),
            'type' => AdTypeEnum::from($request->validated('type')),
            'category' => $request->validated('category') ? AdCategoryEnum::from($request->validated('category')) : null,
            'subcategory' => $request->validated('subcategory') ? AdSubcategoryEnum::from($request->validated('subcategory')) : null,
            'is_urgent' => (bool) $request->validated('is_urgent', false),
            'newImages' => $request->file('images'),
            'deletedMediaIds' => $request->input('deleted_media_ids', []),
        ]);
    }
}
