<?php

namespace App\Http\Controllers;

use App\Services\Ad\AdService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MainController extends Controller
{
    /**
     * Отобразить дашборд с объявлениями и фильтрами
     */
    public function index(Request $request, AdService $service): Response
    {
        // Получаем фильтры из запроса
        $filters = $request->only([
            'type', 'category', 'subcategory', 'location',
            'min_price', 'max_price', 'urgent', 'search'
        ]);

        // Убираем пустые значения
        $filters = array_filter($filters);
        \Log::info('=== DEBUG FILTERS ===');
        \Log::info('Request URL:', ['url' => $request->fullUrl()]);
        \Log::info('Request filters:', $filters);
        $ads = $service->getPublishedAds($filters);
        \Log::info('Ads count returned:', ['count' => $ads->count()]);
        \Log::info('First ad type (if exists):', [
            'type' => $ads->first()?->type?->value ?? 'no ads'
        ]);
        return Inertia::render('Dashboard', [
            'ads' => $ads,
            'filters' => $filters,
        ]);
    }
}
