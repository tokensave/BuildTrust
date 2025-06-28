<?php

namespace App\Http\Controllers;

use App\Services\Ad\AdService;
use Inertia\Inertia;
use Inertia\Response;

class MainController extends Controller
{
    public function index(AdService $service): Response
    {
        $ads = $service->getPublishedAds();
        return Inertia::render('Dashboard', ['ads' => $ads]);
    }
}
