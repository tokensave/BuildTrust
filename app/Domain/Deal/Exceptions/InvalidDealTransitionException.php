<?php

declare(strict_types=1);

namespace App\Domain\Deal\Exceptions;

use App\Domain\Deal\ValueObjects\DealStatus;
use App\Shared\ValueObjects\Ids\DealId;

/**
 * Исключение для недопустимых переходов статусов сделки
 */
final class InvalidDealTransitionException extends \DomainException
{
    public static function fromStatusToStatus(
        DealId $dealId, 
        DealStatus $from, 
        DealStatus $to
    ): self {
        return new self(
            "Нельзя перевести сделку {$dealId->toString()} из статуса '{$from->label()}' в '{$to->label()}'"
        );
    }
    
    public static function dealAlreadyInStatus(DealId $dealId, DealStatus $status): self
    {
        return new self(
            "Сделка {$dealId->toString()} уже находится в статусе '{$status->label()}'"
        );
    }
    
    public static function cannotModifyFinalDeal(DealId $dealId, DealStatus $status): self
    {
        return new self(
            "Нельзя изменить завершенную сделку {$dealId->toString()} в статусе '{$status->label()}'"
        );
    }
}
