<?php

declare(strict_types=1);

namespace App\Domain\Deal\Contracts;

use App\Domain\Deal\Entities\Deal;
use App\Shared\ValueObjects\Ids\DealId;
use App\Shared\ValueObjects\Ids\UserId;

/**
 * Контракт репозитория сделок
 * 
 * Определяет что нужно Domain слою от хранилища данных,
 * но НЕ знает как это реализовано (MySQL, MongoDB, файлы и т.д.)
 */
interface DealRepositoryInterface
{
    /**
     * Сохранить сделку (создать или обновить)
     */
    public function save(Deal $deal): void;

    /**
     * Найти сделку по ID
     */
    public function findById(DealId $id): ?Deal;

    /**
     * Найти все сделки пользователя (как покупателя или продавца)
     */
    public function findByUser(UserId $userId): array;

    /**
     * Найти сделки пользователя как покупателя
     */
    public function findByBuyer(UserId $buyerId): array;

    /**
     * Найти сделки пользователя как продавца
     */
    public function findBySeller(UserId $sellerId): array;

    /**
     * Найти активные сделки (не в финальном статусе)
     */
    public function findActive(): array;

    /**
     * Получить следующий доступный ID
     * (для систем, где ID генерируется в приложении)
     */
    public function nextId(): DealId;

    /**
     * Удалить сделку
     */
    public function delete(DealId $id): void;

    /**
     * Проверить существование сделки
     */
    public function exists(DealId $id): bool;
}
