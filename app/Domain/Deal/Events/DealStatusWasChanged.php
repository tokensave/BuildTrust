<?php

declare(strict_types=1);

namespace App\Domain\Deal\Events;

use App\Domain\Deal\ValueObjects\DealStatus;
use App\Shared\ValueObjects\Ids\DealId;

/**
 * Доменное событие: статус сделки был изменен
 */
final readonly class DealStatusWasChanged
{
    public function __construct(
        public DealId $dealId,
        public DealStatus $previousStatus,
        public DealStatus $newStatus,
        public \DateTimeImmutable $occurredAt
    ) {}
}
