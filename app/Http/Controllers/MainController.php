<?php

namespace App\Http\Controllers;

use App\DTO\Ad\GetAdData;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Models\Ad;
use Inertia\Inertia;
use Inertia\Response;

class MainController extends Controller
{
    public function index(): Response
    {
        $ads = Ad::with('media')
            ->where('status', AdsStatusEnum::PUBLISHED->value)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'ads' => GetAdData::collect($ads)
        ]);
    }
}
