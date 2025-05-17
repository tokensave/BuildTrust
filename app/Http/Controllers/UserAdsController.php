<?php

namespace App\Http\Controllers;

use App\DTO\Ads\AdData;
use App\Http\Requests\Ad\StoreAdsRequest;
use App\Models\Ads;
use App\Models\User;
use App\Services\Ad\AdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
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
    public function store(StoreAdsRequest $request, AdService $service): RedirectResponse
    {
        $data = AdData::fromRequest($request);
        $ad = $service->create($request->user(), $data);

        return to_route('user.ads.store', [$request->user()->id, $ad->id])
            ->with('success', 'Объявление успешно создано');
    }

//    public function edit
    public function destroy(int $user, Ads $ad, AdService $service)
    {
        $service->delete($ad);

        return Inertia::location(route('user.ads.index', $user));
    }
}
