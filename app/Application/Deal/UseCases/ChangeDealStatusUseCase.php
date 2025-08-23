<?php

declare(strict_types=1);

namespace App\Application\Deal\UseCases;

use App\Application\Deal\DTOs\ChangeDealStatusCommand;
use App\Domain\Deal\Contracts\DealRepositoryInterface;
use App\Domain\Deal\Entities\Deal;
use App\Domain\Deal\ValueObjects\DealStatus;
use App\Shared\ValueObjects\Ids\DealId;
use App\Shared\ValueObjects\Ids\UserId;

/**
 * Use Case для изменения статуса сделки
 * 
 * Основная бизнес-логика:
 * 1. Загрузка сделки из репозитория
 * 2. Проверка прав доступа
 * 3. Применение доменных методов
 * 4. Сохранение изменений
 */
final class ChangeDealStatusUseCase
{
    public function __construct(
        private readonly DealRepositoryInterface $dealRepository,
    ) {}

    /**
     * Выполнить изменение статуса сделки
     * 
     * @throws \InvalidArgumentException если операция невозможна
     * @throws \DomainException если нарушены доменные правила
     */
    public function execute(ChangeDealStatusCommand $command): Deal
    {
        // 1. Загружаем сделку из репозитория
        $dealId = DealId::fromInt($command->dealId);
        $deal = $this->dealRepository->findById($dealId);
        
        if (!$deal) {
            throw new \InvalidArgumentException("Сделка с ID {$command->dealId} не найдена");
        }
        
        // 2. Проверяем права доступа
        $userId = UserId::fromInt($command->userId);
        if (!$deal->canBeModifiedBy($userId)) {
            throw new \InvalidArgumentException('У вас нет прав для изменения этой сделки');
        }
        
        // 3. Применяем доменную логику в зависимости от нового статуса
        $newStatus = DealStatus::from($command->newStatus);
        $this->applyStatusChange($deal, $newStatus, $command->reason);
        
        // 4. Сохраняем изменения
        $this->dealRepository->save($deal);
        
        // TODO: Здесь можно добавить dispatch доменных событий
        // EventDispatcher::dispatch($deal->getRecordedEvents());
        
        return $deal;
    }


    /**
     * Применить изменение статуса через доменные методы
     */
    private function applyStatusChange(Deal $deal, DealStatus $newStatus, ?string $reason): void
    {
        switch ($newStatus) {
            case DealStatus::ACCEPTED:
                $deal->accept();
                break;
                
            case DealStatus::REJECTED:
                $deal->reject($reason);
                break;
                
            case DealStatus::COMPLETED:
                $deal->complete();
                break;
                
            case DealStatus::CANCELED:
                if (!$reason) {
                    throw new \InvalidArgumentException('Для отмены сделки необходимо указать причину');
                }
                $deal->cancel($reason);
                break;
                
            case DealStatus::PENDING:
                throw new \InvalidArgumentException('Нельзя вернуть сделку в статус PENDING');
                
            default:
                throw new \InvalidArgumentException("Неизвестный статус: {$newStatus->value}");
        }
    }
}
