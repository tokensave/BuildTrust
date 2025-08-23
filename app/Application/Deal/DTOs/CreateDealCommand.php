<?php

declare(strict_types=1);

namespace App\Application\Deal\DTOs;

use App\Http\Requests\Deal\StoreDealRequest;
use App\Models\Ad;

/**
 * Command DTO для создания сделки
 *
 * Инкапсулирует все данные, необходимые для создания сделки
 * в Application слое. Это "команда" от UI/Controller в Use Case.
 */
final readonly class CreateDealCommand
{
    public function __construct(
        public int $adId,
        public int $buyerId,
        public int $sellerId,
        public float $price,
        public ?string $notes = null,
        public array $documents = [],
    ) {}

    /**
     * Создать команду из HTTP Request данных
     */
    public static function fromArray(array $data): self
    {
        return new self(
            adId: (int) $data['ad_id'],
            buyerId: (int) $data['buyer_id'],
            sellerId: (int) $data['seller_id'],
            price: (float) $data['price'],
            notes: $data['notes'] ?? null,
        );
    }

    /**
     * Создать команду из валидированного Request'а
     * Более удобный метод для контроллеров
     */
    public static function fromRequest(StoreDealRequest $request, Ad $ad, int $buyerId): self
    {
        $validated = $request->validated();
        return new self(
            adId: $ad->id,
            buyerId: $buyerId,
            sellerId: $ad->user_id,
            price: (float) $ad->price,
            notes: $validated['notes'] ?? null,
            documents: $request->file('documents') ?? [],
        );
    }
}
