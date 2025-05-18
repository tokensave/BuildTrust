<?php

namespace App\Http\Controllers;

use App\Enums\AdEnums\AdsStatusEnum;
use App\Models\Ad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    public function index()
    {
        $ads = Ad::with('media')
            ->where('status', AdsStatusEnum::PUBLISHED->value)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ad) {
                $ad->image_url = $ad->getFirstMediaUrl() ?: '/default.jpg';
                $ad->media = $ad->getMedia()->map(fn ($m) => [
                    'original_url' => $m->getUrl(),
                    'thumbnail_url' => $m->getUrl('thumb'),
                ]);
                return $ad;
            });
        return Inertia::render('Dashboard', [
            'ads' => $ads
        ]);
    }
}
