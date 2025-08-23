<?php

declare(strict_types=1);

namespace App\Application\Deal\DTOs;

use App\Http\Requests\Deal\UpdateDealStatusRequest;

/**
 * Command DTO для изменения статуса сделки
 * 
 * Инкапсулирует данные для изменения статуса сделки
 */
final readonly class ChangeDealStatusCommand
{
    public function __construct(
        public int $dealId,
        public string $newStatus,
        public int $userId, // Кто изменяет статус (для проверки прав)
        public ?string $reason = null, // Причина изменения (для отклонения/отмены)
    ) {}

    /**
     * Создать команду из HTTP Request данных
     */
    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            dealId: (int) $data['deal_id'],
            newStatus: (string) $data['status'],
            userId: $userId,
            reason: $data['reason'] ?? null,
        );
    }

    /**
     * Создать команду из валидированного Request'а
     * Более удобный метод для контроллеров
     */
    public static function fromRequest(UpdateDealStatusRequest $request, int $dealId, int $userId): self
    {
        $validated = $request->validated();
        
        return new self(
            dealId: $dealId,
            newStatus: $validated['status'],
            userId: $userId,
            reason: $request->input('reason'), // Может быть не в validated, но в input
        );
    }
}
