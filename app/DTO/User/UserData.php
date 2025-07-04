<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\User;

use App\DTO\Company\CompanyData;
use App\Models\User;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $username,
        public ?CompanyData $company = null // Добавляем компанию
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            company: $user->company ? CompanyData::fromModel($user->company) : null,
        );
    }
}
