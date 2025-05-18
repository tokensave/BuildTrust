<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Ads;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class StoreAdData extends Data
{
    public function __construct(
        public string $title,
        public string $description,
        public ?float $price,
        public ?string $status,
        /** @var UploadedFile[]|null */
        public ?array $images,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return self::from([
            ...$request->validated(),
            'images' => $request->file('images'),
        ]);
    }
}
