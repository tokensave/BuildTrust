<?php

declare(strict_types=1);

namespace App\Domain\Deal\Entities;

use App\Domain\Deal\Events\DealStatusWasChanged;
use App\Domain\Deal\Events\DealWasCreated;
use App\Domain\Deal\Exceptions\InvalidDealTransitionException;
use App\Domain\Deal\ValueObjects\DealNotes;
use App\Domain\Deal\ValueObjects\DealStatus;
use App\Shared\ValueObjects\Ids\AdId;
use App\Shared\ValueObjects\Ids\DealId;
use App\Shared\ValueObjects\Ids\UserId;
use App\Shared\ValueObjects\Money;

/**
 * Главная доменная сущность сделки
 * 
 * Содержит всю бизнес-логику работы со сделками:
 * - Создание и валидация
 * - Переходы между статусами  
 * - Генерация доменных событий
 * - Защита от некорректных операций
 */
final class Deal
{
    /** @var object[] Записанные доменные события */
    private array $recordedEvents = [];

    private function __construct(
        private readonly DealId $id,
        private readonly AdId $adId,
        private readonly UserId $buyerId,
        private readonly UserId $sellerId,
        private readonly Money $price,
        private DealStatus $status,
        private ?DealNotes $notes = null,
        private ?string $onChainId = null,
        private ?string $uuid = null,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $signedAt = null
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
        $this->uuid ??= \Illuminate\Support\Str::uuid()->toString();
    }

    // === ФАБРИКИ ===

    /**
     * Создать новую сделку (фабрика для создания)
     */
    public static function create(
        DealId $id,
        AdId $adId,
        UserId $buyerId,
        UserId $sellerId,
        Money $price,
        ?DealNotes $notes = null
    ): self {
        // Генерируем UUID до создания объекта
        $uuid = \Illuminate\Support\Str::uuid()->toString();
        
        $deal = new self(
            id: $id,
            adId: $adId,
            buyerId: $buyerId,
            sellerId: $sellerId,
            price: $price,
            status: DealStatus::PENDING,
            notes: $notes,
            uuid: $uuid
        );

        // Записываем событие создания
        $deal->recordThat(new DealWasCreated(
            dealId: $id,
            dealUuid: $uuid,
            adId: $adId,
            buyerId: $buyerId,
            sellerId: $sellerId,
            price: $price,
            occurredAt: new \DateTimeImmutable()
        ));

        return $deal;
    }

    /**
     * Восстановить сделку из БД (фабрика для репозитория)
     */
    public static function reconstruct(
        DealId $id,
        AdId $adId,
        UserId $buyerId,
        UserId $sellerId,
        Money $price,
        DealStatus $status,
        ?DealNotes $notes = null,
        ?string $onChainId = null,
        ?string $uuid = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $signedAt = null
    ): self {
        return new self(
            id: $id,
            adId: $adId,
            buyerId: $buyerId,
            sellerId: $sellerId,
            price: $price,
            status: $status,
            notes: $notes,
            onChainId: $onChainId,
            uuid: $uuid,
            createdAt: $createdAt,
            signedAt: $signedAt
        );
    }

    // === БИЗНЕС-ОПЕРАЦИИ ===

    /**
     * Принять сделку (PENDING → ACCEPTED)
     */
    public function accept(): void
    {
        $this->changeStatus(DealStatus::ACCEPTED);
    }

    /**
     * Отклонить сделку (PENDING → REJECTED)
     */
    public function reject(?string $reason = null): void
    {
        if ($reason) {
            $this->addNote("Причина отклонения: " . $reason);
        }
        
        $this->changeStatus(DealStatus::REJECTED);
    }

    /**
     * Завершить сделку (ACCEPTED → COMPLETED)
     */
    public function complete(): void
    {
        $this->changeStatus(DealStatus::COMPLETED);
        $this->signedAt = new \DateTimeImmutable();
    }

    /**
     * Отменить сделку (PENDING/ACCEPTED → CANCELED)
     */
    public function cancel(string $reason): void
    {
        $this->addNote("Причина отмены: " . $reason);
        $this->changeStatus(DealStatus::CANCELED);
    }

    /**
     * Добавить заметку к сделке
     */
    public function addNote(string $note): void
    {
        if ($this->notes) {
            $this->notes = $this->notes->append($note);
        } else {
            $this->notes = DealNotes::fromString($note);
        }
    }

    /**
     * Отметить сделку как записанную в блокчейн
     */
    public function markAsOnChain(string $onChainId): void
    {
        if ($this->onChainId) {
            return; // Уже записана
        }
        
        $this->onChainId = $onChainId;
    }

    // === БИЗНЕС-ПРАВИЛА (ПРОВЕРКИ) ===

    /**
     * Проверить, может ли пользователь выполнить операцию со сделкой
     */
    public function canBeModifiedBy(UserId $userId): bool
    {
        return $this->buyerId->equals($userId) || $this->sellerId->equals($userId);
    }

    /**
     * Проверить, является ли пользователь покупателем
     */
    public function isBuyer(UserId $userId): bool
    {
        return $this->buyerId->equals($userId);
    }

    /**
     * Проверить, является ли пользователь продавцом
     */
    public function isSeller(UserId $userId): bool
    {
        return $this->sellerId->equals($userId);
    }

    /**
     * Проверить, записана ли сделка в блокчейн
     */
    public function isOnChain(): bool
    {
        return !empty($this->onChainId);
    }

    // === ГЕТТЕРЫ ===

    public function getId(): DealId
    {
        return $this->id;
    }

    public function getAdId(): AdId
    {
        return $this->adId;
    }

    public function getBuyerId(): UserId
    {
        return $this->buyerId;
    }

    public function getSellerId(): UserId
    {
        return $this->sellerId;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getStatus(): DealStatus
    {
        return $this->status;
    }

    public function getNotes(): ?DealNotes
    {
        return $this->notes;
    }

    public function getOnChainId(): ?string
    {
        return $this->onChainId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSignedAt(): ?\DateTimeImmutable
    {
        return $this->signedAt;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    // === СОБЫТИЯ ===

    /**
     * Получить все записанные события
     */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    /**
     * Очистить записанные события (после обработки)
     */
    public function clearRecordedEvents(): void
    {
        $this->recordedEvents = [];
    }

    // === ПРИВАТНЫЕ МЕТОДЫ ===

    /**
     * Изменить статус с проверками и событиями
     */
    private function changeStatus(DealStatus $newStatus): void
    {
        // Проверяем, что статус действительно изменился
        if ($this->status === $newStatus) {
            throw InvalidDealTransitionException::dealAlreadyInStatus($this->id, $newStatus);
        }

        // Проверяем, можно ли сделать переход
        if (!$this->status->canTransitionTo($newStatus)) {
            throw InvalidDealTransitionException::fromStatusToStatus($this->id, $this->status, $newStatus);
        }

        $previousStatus = $this->status;
        $this->status = $newStatus;

        // Записываем событие изменения статуса
        $this->recordThat(new DealStatusWasChanged(
            dealId: $this->id,
            previousStatus: $previousStatus,
            newStatus: $newStatus,
            occurredAt: new \DateTimeImmutable()
        ));
    }

    /**
     * Записать доменное событие
     */
    private function recordThat(object $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
