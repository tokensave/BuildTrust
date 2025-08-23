<?php

namespace App\Http\Controllers;

use App\Application\Deal\DTOs\CreateDealCommand;
use App\Application\Deal\DTOs\ChangeDealStatusCommand;
use App\Application\Deal\UseCases\CreateDealUseCase;
use App\Application\Deal\UseCases\ChangeDealStatusUseCase;
use App\Application\Deal\UseCases\GetUserDealsUseCase;
use App\Http\Requests\Deal\StoreDealRequest;
use App\Http\Requests\Deal\UpdateDealStatusRequest;
use App\Models\Ad;
use App\Models\Deal;
use App\Domain\Deal\ValueObjects\DealStatus;
use App\Services\Deal\DealService; // Временно для index
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DealController extends Controller
{
    /**
     * Получить сделки пользователя
     * TODO: Позже мигрируем на GetUserDealsUseCase когда добавим eager loading
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
     * Создание сделки через DDD архитектуру
     */
    public function store(StoreDealRequest $request, Ad $ad, CreateDealUseCase $createDealUseCase)
    {
        // Создаем команду из валидированных данных
        $command = CreateDealCommand::fromRequest($request, $ad, auth()->id());
        $deal = $createDealUseCase->execute($command);

        return Inertia::location(route('user.ads.show', [
            'user' => $ad->user_id,
            'ad' => $ad->id,
        ]));
    }

    /**
     * Изменение статуса сделки через DDD архитектуру
     */
    public function updateStatus(
        UpdateDealStatusRequest $request,
        Deal $deal,
        ChangeDealStatusUseCase $changeDealStatusUseCase
    ): RedirectResponse {
        try {
            // Создаем команду из валидированных данных
            $command = ChangeDealStatusCommand::fromRequest($request, $deal->id, auth()->id());
            $updatedDeal = $changeDealStatusUseCase->execute($command);

            return to_route('user.deals.index')->with([
                'success' => 'Статус сделки успешно изменен на: ' . $updatedDeal->getStatus()->label()
            ]);

        } catch (\InvalidArgumentException|\DomainException $e) {
            return to_route('user.deals.index')->with([
                'error' => $e->getMessage()
            ]);
        }
    }
}
