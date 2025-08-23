<?php

declare(strict_types=1);

namespace App\Application\Deal\UseCases;

use App\Domain\Deal\Contracts\DealRepositoryInterface;
use App\Domain\Deal\Entities\Deal;
use App\Shared\ValueObjects\Ids\UserId;

/**
 * Use Case для получения сделок пользователя
 * 
 * Основная бизнес-логика:
 * 1. Получение сделок пользователя через репозиторий
 * 2. Фильтрация по различным критериям
 * 3. Возврат отфильтрованных сделок
 */
final class GetUserDealsUseCase
{
    public function __construct(
        private readonly DealRepositoryInterface $dealRepository,
    ) {}

    /**
     * Получить все сделки пользователя (как покупателя, так и продавца)
     * 
     * @return Deal[]
     */
    public function execute(int $userId): array
    {
        $userIdVO = UserId::fromInt($userId);
        
        return $this->dealRepository->findByUser($userIdVO);
    }

    /**
     * Получить только сделки где пользователь выступает покупателем
     * 
     * @return Deal[]
     */
    public function executeForBuyer(int $userId): array
    {
        $userIdVO = UserId::fromInt($userId);
        
        return $this->dealRepository->findByBuyer($userIdVO);
    }

    /**
     * Получить только сделки где пользователь выступает продавцом
     * 
     * @return Deal[]
     */
    public function executeForSeller(int $userId): array
    {
        $userIdVO = UserId::fromInt($userId);
        
        return $this->dealRepository->findBySeller($userIdVO);
    }

    /**
     * Получить только активные сделки пользователя
     * 
     * @return Deal[]
     */
    public function executeActiveOnly(int $userId): array
    {
        $userIdVO = UserId::fromInt($userId);
        $allDeals = $this->dealRepository->findByUser($userIdVO);
        
        // Фильтруем только активные сделки
        return array_filter($allDeals, fn(Deal $deal) => $deal->getStatus()->isActive());
    }

    /**
     * Получить сделки в блокчейне (записанные on-chain)
     * 
     * @return Deal[]
     */
    public function executeOnChainOnly(int $userId): array
    {
        $userIdVO = UserId::fromInt($userId);
        $allDeals = $this->dealRepository->findByUser($userIdVO);
        
        // Фильтруем только сделки записанные в блокчейн
        return array_filter($allDeals, fn(Deal $deal) => $deal->isOnChain());
    }
}
