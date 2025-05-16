<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
}
