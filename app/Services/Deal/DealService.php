<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Deal;

use App\DTO\Deal\StoreDealData;
use App\Models\Ad;
use App\Models\Deal;
use Illuminate\Support\Facades\DB;

class DealService
{
    /**
     * @throws \Throwable
     */
    public function createDeal(StoreDealData $data, Ad $ad): Deal
    {
        return DB::transaction(static function () use ($data, $ad) {
            $deal = Deal::create([
                'ad_id' => $ad->id,
                'buyer_id' => auth()->id(),
                'seller_id' => $ad->user_id,
                'price' => $ad->price,
                'notes' => $data->notes,
            ]);

            if (!empty($data->documents)) {
                foreach ($data->documents as $file) {
                    $deal->addMedia($file)->toMediaCollection('documents');
                }
            }

            return $deal;
        });
    }
}
