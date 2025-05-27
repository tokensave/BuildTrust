<?php

namespace App\Listeners;

use App\Events\DealCreatedOrUpdatedEvent;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendDealToBlockchainMiddleware
{

    /**
     */
    public function handle(DealCreatedOrUpdatedEvent $event): void
    {
        $deal = $event->deal;
        try {
            // Отправляем данные в промежуточный модуль на Go
            Http::timeout(10)->post(config('app.blockchain_api_url') . '/save-deal', [
                'deal_id' => $deal->id,
                'unique_id' => $deal->uuid,
                'ad_id' => $deal->ad_id,
                'buyer_id' => $deal->buyer_id,
                'seller_id' => $deal->seller_id,
                'price' => (float)$deal->price,
                'status' => $deal->status,
                'notes' => $deal->notes,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send deal to blockchain-transport', [
                'error' => $e->getMessage(),
                'url' => config('app.blockchain_api_url') . '/save-deal',
                'deal_id' => $deal->id,
            ]);
        }
    }
}
