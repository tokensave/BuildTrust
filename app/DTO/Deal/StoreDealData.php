<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Deal;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class StoreDealData extends Data
{
    public function __construct(
        public ?string $notes,
        /** @var UploadedFile[]|null */
        public ?array $documents,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return self::from([
            ...$request->validated(),
            'documents' => $request->file('documents'),
        ]);
    }
}
