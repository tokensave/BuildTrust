<?php

declare(strict_types=1);

namespace App\Infrastructure\Deal\Listeners;

use App\Domain\Deal\Events\DealWasCreated;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Infrastructure Listener для отправки новой сделки в блокчейн микросервис
 * 
 * Слушает доменное событие DealWasCreated и отправляет данные в Go микросервис
 * Основан на существующем SendDealToBlockchainMiddleware
 */
class SendNewDealToBlockchain
{
    /**
     * Обработать событие создания сделки
     */
    public function handle(DealWasCreated $event): void
    {
        try {
            // Подготавливаем данные для микросервиса (аналогично существующему коду)
            $payload = [
                'deal_id' => $event->dealId->toInt(),
                'unique_id' => $event->dealUuid, // Используем UUID из доменного события
                'ad_id' => $event->adId->toInt(),
                'buyer_id' => $event->buyerId->toInt(),
                'seller_id' => $event->sellerId->toInt(),
                'price' => $event->price->toRubles(),
                'status' => 'pending', // Новые сделки всегда PENDING
                'notes' => null, // В событии создания пока нет заметок
            ];

            // Отправляем в Go микросервис (используем тот же endpoint)
            Http::timeout(10)->post(config('app.blockchain_api_url') . '/save-deal', $payload);
            
            Log::info('New deal sent to blockchain microservice via domain event', [
                'deal_id' => $event->dealId->toInt(),
                'ad_id' => $event->adId->toInt(),
                'microservice_url' => config('app.blockchain_api_url'),
            ]);

        } catch (Exception $e) {
            // Логируем ошибку, но НЕ ломаем основной флоу создания сделки
            Log::error('Failed to send new deal to blockchain microservice', [
                'error' => $e->getMessage(),
                'url' => config('app.blockchain_api_url') . '/save-deal',
                'deal_id' => $event->dealId->toInt(),
                'trace' => $e->getTraceAsString()
            ]);

            // TODO: Можно добавить retry через очередь
            // dispatch(new RetryDealBlockchainJob($event))->delay(60);
        }
    }
}
