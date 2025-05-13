<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\UserSetting;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class UserProfileData extends Data
{
    public function __construct(
        public ?string $username,
        public string $email,
        public ?string $company_name,
        public ?string $inn,
        public ?string $phone,
        public ?string $city,
        public ?string $address,
        public ?string $website,
        public ?bool $verified,
        public ?UploadedFile $avatar,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return self::from([
            ...$request->validated(),
            'avatar' => $request->file('avatar'),
        ]);
    }
}
