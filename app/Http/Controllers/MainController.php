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
        
        // Получаем пагинированные объявления (теперь с Scout поддержкой)
        $adsData = $service->getPublishedAds($filters, 10);
        
        return Inertia::render('Dashboard', [
            // Передаем структуру пагинации
            'ads' => [
                'data' => $adsData->items(), // Сами объявления
                'current_page' => $adsData->currentPage(),
                'last_page' => $adsData->lastPage(),
                'per_page' => $adsData->perPage(),
                'total' => $adsData->total(),
                'from' => $adsData->firstItem(),
                'to' => $adsData->lastItem(),
            ],
            'filters' => $filters,
        ]);
    }
}
