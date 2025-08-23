<?php

declare(strict_types=1);

namespace App\Infrastructure\Deal\Repositories;

use App\Domain\Deal\Contracts\DealRepositoryInterface;
use App\Domain\Deal\Entities\Deal;
use App\Domain\Deal\ValueObjects\DealNotes;
use App\Domain\Deal\ValueObjects\DealStatus;
use App\Infrastructure\Deal\Models\DealModel;
use App\Shared\ValueObjects\Ids\AdId;
use App\Shared\ValueObjects\Ids\DealId;
use App\Shared\ValueObjects\Ids\UserId;
use App\Shared\ValueObjects\Money;

/**
 * Eloquent реализация репозитория сделок
 * 
 * Основная задача: преобразование между Domain Entity и Infrastructure Model
 * 
 * Domain Entity ←→ Infrastructure Model
 *      ↑                      ↓
 *   (память)              (база данных)
 */
final class EloquentDealRepository implements DealRepositoryInterface
{
    public function save(Deal $deal): void
    {
        // Ищем существующую модель или создаем новую
        $model = DealModel::find($deal->getId()->toInt()) ?? new DealModel();

        // Преобразуем Domain Entity → Infrastructure Model
        $model->fill([
            'ad_id' => $deal->getAdId()->toInt(),
            'buyer_id' => $deal->getBuyerId()->toInt(),
            'seller_id' => $deal->getSellerId()->toInt(),
            'price' => $deal->getPrice()->toRubles(),  // Money → float
            'status' => $deal->getStatus()->value,     // Enum → string
            'notes' => $deal->getNotes()?->toString(), // DealNotes → string|null
            'on_chain_id' => $deal->getOnChainId(),
            'uuid' => $deal->getUuid(),                // UUID
            'created_at' => $deal->getCreatedAt(),
            'signed_at' => $deal->getSignedAt(),
        ]);

        $model->save();
    }

    public function findById(DealId $id): ?Deal
    {
        $model = DealModel::find($id->toInt());

        if (!$model) {
            return null;
        }

        // Преобразуем Infrastructure Model → Domain Entity
        return $this->toDomain($model);
    }

    public function findByUser(UserId $userId): array
    {
        $models = DealModel::where('buyer_id', $userId->toInt())
            ->orWhere('seller_id', $userId->toInt())
            ->get();

        return $this->toDomainCollection($models);
    }

    public function findByBuyer(UserId $buyerId): array
    {
        $models = DealModel::where('buyer_id', $buyerId->toInt())->get();
        
        return $this->toDomainCollection($models);
    }

    public function findBySeller(UserId $sellerId): array
    {
        $models = DealModel::where('seller_id', $sellerId->toInt())->get();
        
        return $this->toDomainCollection($models);
    }

    public function findActive(): array
    {
        // Активные сделки - не в финальном статусе
        $models = DealModel::whereIn('status', [
            DealStatus::PENDING->value,
            DealStatus::ACCEPTED->value,
        ])->get();

        return $this->toDomainCollection($models);
    }

    public function nextId(): DealId
    {
        // Получаем следующий ID (для систем где ID генерируется в приложении)
        $maxId = DealModel::max('id') ?? 0;
        
        return DealId::fromInt($maxId + 1);
    }

    public function delete(DealId $id): void
    {
        DealModel::where('id', $id->toInt())->delete();
    }

    public function exists(DealId $id): bool
    {
        return DealModel::where('id', $id->toInt())->exists();
    }

    // === ПРИВАТНЫЕ МЕТОДЫ ПРЕОБРАЗОВАНИЯ ===

    /**
     * Преобразует Infrastructure Model → Domain Entity
     * Это ключевой метод! Здесь происходит восстановление доменного объекта из БД.
     */
    private function toDomain(DealModel $model): Deal
    {
        return Deal::reconstruct(
            id: DealId::fromInt($model->id),
            adId: AdId::fromInt($model->ad_id),
            buyerId: UserId::fromInt($model->buyer_id),
            sellerId: UserId::fromInt($model->seller_id),
            price: Money::fromRubles((float) $model->price), // float → Money
            status: DealStatus::from($model->status),        // string → Enum
            notes: DealNotes::fromString($model->notes),     // string|null → DealNotes|null
            onChainId: $model->on_chain_id,
            uuid: $model->uuid,                              // UUID
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at) : null,
            signedAt: $model->signed_at ? \DateTimeImmutable::createFromMutable($model->signed_at) : null
        );
    }

    /**
     * Преобразует коллекцию Infrastructure Models → Domain Entities
     */
    private function toDomainCollection($models): array
    {
        return $models->map(fn(DealModel $model) => $this->toDomain($model))->toArray();
    }
}
