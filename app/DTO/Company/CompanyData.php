<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Company;

use App\Models\Company;
use Spatie\LaravelData\Data;

class CompanyData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $address
    ) {}

    public static function fromModel(Company $company): self
    {
        return new self(
            id: $company->id,
            name: $company->name,
            address: $company->address,
        );
    }
}
