<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Ad;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class UpdateAdData extends Data
{
    public function __construct(
        public string $title,
        public string $description,
        public ?float $price,
        public string $status,
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
            'newImages' => $request->file('images'),
            'deletedMediaIds' => $request->input('deleted_media_ids', []),
        ]);
    }
}
