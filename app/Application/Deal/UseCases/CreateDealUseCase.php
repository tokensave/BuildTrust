<?php

declare(strict_types=1);

namespace App\Application\Deal\UseCases;

use App\Application\Deal\DTOs\CreateDealCommand;
use App\Domain\Deal\Contracts\DealRepositoryInterface;
use App\Domain\Deal\Entities\Deal;
use App\Domain\Deal\ValueObjects\DealNotes;
use App\Infrastructure\Deal\Models\DealModel;
use App\Shared\ValueObjects\Ids\AdId;
use App\Shared\ValueObjects\Ids\DealId;
use App\Shared\ValueObjects\Ids\UserId;
use App\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\Event;

/**
 * Use Case для создания сделки
 *
 * Основная бизнес-логика:
 * 1. Валидация данных
 * 2. Создание доменной сущности
 * 3. Сохранение через репозиторий
 * 4. Возврат созданной сделки
 */
final class CreateDealUseCase
{
    public function __construct(
        private readonly DealRepositoryInterface $dealRepository,
    ) {}

    /**
     * Выполнить создание сделки
     *
     * @throws \InvalidArgumentException если данные некорректны
     */
    public function execute(CreateDealCommand $command): Deal
    {
        // 1. Валидация бизнес-правил
        $this->validateCommand($command);
        // 2. Генерируем ID для новой сделки
        $dealId = $this->dealRepository->nextId();

        // 3. Создаем Value Objects из примитивных данных
        $adId = AdId::fromInt($command->adId);
        $buyerId = UserId::fromInt($command->buyerId);
        $sellerId = UserId::fromInt($command->sellerId);
        $price = Money::fromRubles($command->price);
        $notes = $command->notes ? DealNotes::fromString($command->notes) : null;

        // 4. Создаем доменную сущность (со всеми доменными событиями)
        $deal = Deal::create(
            id: $dealId,
            adId: $adId,
            buyerId: $buyerId,
            sellerId: $sellerId,
            price: $price,
            notes: $notes
        );

        // 5. Сохраняем через репозиторий
        $this->dealRepository->save($deal);

        // 6. Обрабатываем документы (темпорарно через Infrastructure Model)
        if (!empty($command->documents)) {
            $this->handleDocuments($deal->getId()->toInt(), $command->documents);
        }

        // 7. Отправляем доменные события
        foreach ($deal->getRecordedEvents() as $domainEvent) {
            Event::dispatch($domainEvent);
        }
        $deal->clearRecordedEvents();

        return $deal;
    }


    /**
     * Валидация бизнес-правил
     */
    private function validateCommand(CreateDealCommand $command): void
    {
        if ($command->buyerId === $command->sellerId) {
            throw new \InvalidArgumentException('Покупатель и продавец не могут быть одним лицом');
        }

        if ($command->price <= 0) {
            throw new \InvalidArgumentException('Цена должна быть положительной');
        }

        // TODO: Можно добавить проверку существования Ad, User'ов и т.д.
        // Или вынести это в отдельные Validator'ы
    }

    /**
     * Обработать документы (темпорарное решение)
     * TODO: Позже вынести в отдельный DocumentService или MediaLibraryService
     */
    private function handleDocuments(int $dealId, array $documents): void
    {
        $dealModel = DealModel::find($dealId);
        if (!$dealModel) {
            return; // Модель не найдена, пропускаем
        }

        foreach ($documents as $file) {
            if ($file && $file->isValid()) {
                $dealModel->addMedia($file)->toMediaCollection('documents');
            }
        }
    }
}
