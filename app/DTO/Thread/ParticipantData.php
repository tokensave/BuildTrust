<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\DTO\Thread;

use App\Models\User;
use Spatie\LaravelData\Data;

class ParticipantData extends Data
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
    ){}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            email: $user->email,
        );
    }
}
