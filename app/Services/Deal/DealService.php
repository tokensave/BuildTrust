<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\Deal;

use App\DTO\Deal\StoreDealData;
use App\Enums\DealEnums\DealStatusEnum;
use App\Events\DealCreatedOrUpdatedEvent;
use App\Models\Ad;
use App\Models\Deal;
use Illuminate\Support\Collection;
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

            event(new DealCreatedOrUpdatedEvent($deal));
            return $deal;
        });
    }

    public function getDeals(): Collection
    {
        return Deal::with([
            'buyer.company',
            'seller.company',
            'media',
            'ad'
        ])
            ->where(function ($query) {
                $query->where('buyer_id', auth()->id())
                    ->orWhere('seller_id', auth()->id());
            })
            ->get()
            ->map(function ($deal) {
                return [
                    'id' => $deal->id,
                    'ad_title' => $deal->ad->title ?? null,
                    'price' => $deal->price,
                    'status' => $deal->status,
                    'notes' => $deal->notes,
                    'created_at' => $deal->created_at->toDateString(),
                    'documents' => $deal->getMedia('documents')->map(fn($media) => [
                        'id' => $media->id,
                        'name' => $media->name,
                        'url' => $media->getUrl(),
                    ]),
                    'buyer' => [
                        'id' => $deal->buyer->id,
                        'name' => $deal->buyer->username,
                        'company' => optional($deal->buyer->company)->name,
                    ],
                    'seller' => [
                        'id' => $deal->seller->id,
                        'name' => $deal->seller->username,
                        'company' => optional($deal->seller->company)->name,
                    ],
                ];
            });
    }

    public function getStatuses(): array
    {
        return collect(DealStatusEnum::cases())->mapWithKeys(function ($case) {
            return [
                $case->value => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ]
            ];
        })->toArray();
    }

    public function updateStatus(Deal $deal, string $status): Deal
    {
        $deal->update(['status' => $status]);
        return $deal;
    }
}
