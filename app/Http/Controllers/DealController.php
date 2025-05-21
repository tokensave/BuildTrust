<?php

namespace App\Http\Controllers;

use App\DTO\Deal\StoreDealData;
use App\Http\Requests\Deal\StoreDealRequest;
use App\Models\Ad;
use App\Services\Deal\DealService;
use Inertia\Inertia;

class DealController extends Controller
{
    public function store(StoreDealRequest $request, Ad $ad, DealService $dealService)
    {
        $data = StoreDealData::fromRequest($request);

        $dealService->createDeal($data, $ad);

        return Inertia::location(route('user.ads.show', [
            'user' => $ad->user_id,
            'ad' => $ad->id,
        ]));
    }
}
