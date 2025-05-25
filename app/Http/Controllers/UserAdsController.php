<?php

namespace App\Http\Controllers;

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

class UserAdsController extends Controller
{
    public function index(User $user): Response
    {
        $ads = $user->ads()->with('media')->get()->map(function ($ad) {
            return [
                'id' => $ad->id,
                'title' => $ad->title,
                'description' => $ad->description,
                'image_url' => $ad->getFirstMediaUrl('images') ?: '/images/default-ad.png',
                'user_id' => $ad->user_id,
                'status' => $ad->status->value,
            ];
        });

        return Inertia::render('ads/userAds/Index', [
            'ads' => $ads,
        ]);
    }

    public function show(int $user, int $adId): Response
    {
        $ad = Ad::with('media', 'user.company')->findOrFail($adId);

        return Inertia::render('ads/userAds/Show', [
            'auth' => [
                'user' => auth()->user(),
            ],
            'ad' => $ad,
        ]);
    }

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
     * @throws FileIsTooBig
     */
    public function store(StoreAdRequest $request, AdService $service): RedirectResponse
    {
        $data = StoreAdData::fromRequest($request);
        $ad = $service->create($request->user(), $data);

        return to_route('user.ads.store', [$request->user()->id, $ad->id])
            ->with('success', 'Объявление успешно создано');
    }

    public function edit(int $user, int $adId): Response
    {
        $ad = Ad::with('media')->findOrFail($adId);

        return Inertia::render('ads/userAds/Edit', [
            'auth' => [
                'user' => auth()->user(),
            ],
            'ad' => $ad,
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(UpdateAdRequest $request, int $user, Ad $ad, AdService $service): RedirectResponse
    {
        $data = UpdateAdData::fromRequest($request);

        $service->update($ad, $data);

        return to_route('user.ads.index', $user)
            ->with('success', 'Объявление успешно обновлено');
    }
    public function destroy(int $user, Ad $ad, AdService $service)
    {
        // TODO необходимо доработать механизм когда если у обьявления есть сделка ,
        // то удалить невозможно только если статус отклонен
        $service->delete($ad);

        return Inertia::location(route('user.ads.index', $user));
    }
}
