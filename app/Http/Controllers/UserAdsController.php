<?php

namespace App\Http\Controllers;

use App\DTO\Ad\GetAdData;
use App\DTO\Ad\ShowAdData;
use App\DTO\Ad\StoreAdData;
use App\DTO\Ad\UpdateAdData;
use App\Http\Requests\Ad\StoreAdRequest;
use App\Http\Requests\Ad\UpdateAdRequest;
use App\Models\Ad;
use App\Models\User;
use App\Services\Ad\AdService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class UserAdsController extends Controller
{
    /**
     * @param User $user
     * @param AdService $service
     * @return Response
     */
    public function index(User $user, AdService $service): Response
    {
        $ads = $service->getUserAds($user);
        return Inertia::render('ads/userAds/Index', ['ads' => $ads,]);
    }

    /**
     * @param int $user
     * @param int $adId
     * @param AdService $service
     * @return Response
     */
    public function show(int $user, int $adId, AdService $service): Response
    {
        $ad = $service->findByIdWithCompany($adId);
        return Inertia::render('ads/userAds/Show', [
            'auth' => ['user' => auth()->user()],
            'ad' => $ad,
        ]);
    }

    /**
     * @param int $user
     * @return Response
     */
    public function create(int $user): Response
    {
        return Inertia::render('ads/userAds/Create', [
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig|Throwable
     */
    public function store(StoreAdRequest $request, AdService $service): RedirectResponse
    {
        $ad = $service->create($request->user(), StoreAdData::fromRequest($request));
        return to_route('user.ads.store', [$request->user()->id, $ad->id])
            ->with('success', 'Объявление успешно создано');
    }

    /**
     * @param int $user
     * @param int $adId
     * @param AdService $service
     * @return Response
     */
    public function edit(int $user, int $adId, AdService $service): Response
    {
        $ad = $service->findById($adId);
        return Inertia::render('ads/userAds/Edit', [
            'auth' => ['user' => auth()->user()],
            'ad' => $ad,
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig|Throwable
     */
    public function update(UpdateAdRequest $request, int $user, Ad $ad, AdService $service): RedirectResponse
    {
        $service->update($ad, UpdateAdData::fromRequest($request));
        return to_route('user.ads.index', $user)
            ->with('success', 'Объявление успешно обновлено');
    }

    /**
     * @param int $user
     * @param Ad $ad
     * @param AdService $service
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(int $user, Ad $ad, AdService $service)
    {
        if (!$service->checkDeal($ad))
        {
            $service->delete($ad);
        } else {
            return redirect()->back()->with('fail', 'Невозможно удалить объявление при наличии активной сделки.');
        }
        return to_route('user.ads.index', $user);
    }
}
