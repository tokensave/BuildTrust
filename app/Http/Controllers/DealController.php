<?php

namespace App\Http\Controllers;

use App\DTO\Deal\StoreDealData;
use App\Http\Requests\Deal\StoreDealRequest;
use App\Http\Requests\Deal\UpdateDealStatusRequest;
use App\Models\Ad;
use App\Models\Deal;
use App\Services\Deal\DealService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DealController extends Controller
{
    /**
     * @param DealService $dealService
     * @return Response
     */
    public function index(DealService $dealService): Response
    {
        $deals = $dealService->getDeals();
        $statuses = $dealService->getStatuses();
        return Inertia::render('userDeals/Index', [
            'deals' => $deals,
            'statuses' => $statuses,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreDealRequest $request, Ad $ad, DealService $dealService)
    {
        $dealService->createDeal(StoreDealData::fromRequest($request), $ad);
        return Inertia::location(route('user.ads.show', [
            'user' => $ad->user_id,
            'ad' => $ad->id,
        ]));
    }

    /**
     * @param UpdateDealStatusRequest $request
     * @param Deal $deal
     * @param DealService $dealService
     * @return RedirectResponse
     */
    public function updateStatus(UpdateDealStatusRequest $request, Deal $deal, DealService $dealService)
    {
        $dealService->updateStatus($deal, $request->status);
        return to_route('user.deals.index')->with(['success' => 'Статус успешно обновлен']);
    }
}
