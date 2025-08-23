<?php

declare(strict_types=1);

namespace App\Domain\Deal\Events;

use App\Domain\Deal\ValueObjects\DealStatus;
use App\Shared\ValueObjects\Ids\AdId;
use App\Shared\ValueObjects\Ids\DealId;
use App\Shared\ValueObjects\Ids\UserId;
use App\Shared\ValueObjects\Money;

/**
 * Доменное событие: сделка была создана
 * 
 * Это НЕ Laravel Event! Это чистое доменное событие.
 * Содержит только данные о том, что произошло в бизнесе.
 */
final readonly class DealWasCreated
{
    public function __construct(
        public DealId $dealId,
        public string $dealUuid,
        public AdId $adId,
        public UserId $buyerId,
        public UserId $sellerId,
        public Money $price,
        public \DateTimeImmutable $occurredAt
    ) {}
}
