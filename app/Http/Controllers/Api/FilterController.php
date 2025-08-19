<?php

namespace App\Http\Controllers\Api;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
use App\Http\Controllers\Controller;
use App\Services\Ad\AdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    /**
     * Получить все типы объявлений
     */
    public function getTypes(): JsonResponse
    {
        return response()->json(AdTypeEnum::toArray());
    }

    /**
     * Получить все категории
     */
    public function getCategories(): JsonResponse
    {
        return response()->json(AdCategoryEnum::toArray());
    }

    /**
     * Получить категории по типу
     */
    public function getCategoriesByType(Request $request): JsonResponse
    {
        $type = $request->get('type');

        if (!$type) {
            return response()->json(['error' => 'Type parameter is required'], 400);
        }

        try {
            $typeEnum = AdTypeEnum::from($type);
            $categories = AdCategoryEnum::getByType($typeEnum);

            $result = collect($categories)->mapWithKeys(fn($category) => [
                $category->value => [
                    'value' => $category->value,
                    'label' => $category->label(),
                ]
            ]);

            return response()->json($result);
        } catch (\ValueError $e) {
            return response()->json(['error' => 'Invalid type'], 400);
        }
    }

    /**
     * Получить подкатегории по категории
     */
    public function getSubcategories(Request $request): JsonResponse
    {
        $category = $request->get('category');

        if (!$category) {
            return response()->json(['error' => 'Category parameter is required'], 400);
        }

        try {
            $categoryEnum = AdCategoryEnum::from($category);
            $subcategories = AdSubcategoryEnum::getByCategory($categoryEnum);

            $result = collect($subcategories)->mapWithKeys(fn($subcategory) => [
                $subcategory->value => [
                    'value' => $subcategory->value,
                    'label' => $subcategory->label(),
                ]
            ]);

            return response()->json($result);
        } catch (\ValueError $e) {
            return response()->json(['error' => 'Invalid category'], 400);
        }
    }

    /**
     * Получить всю структуру категорий и подкатегорий
     */
    public function getAllStructure(): JsonResponse
    {
        return response()->json([
            'types' => AdTypeEnum::toArray(),
            'categories_structure' => AdSubcategoryEnum::toArrayGrouped()
        ]);
    }

    /**
     * Получить популярные местоположения
     */
    public function getPopularLocations(): JsonResponse
    {
        // Здесь можно добавить логику для получения популярных городов
        // Пока возвращаем статичный список
        $locations = [
            'moscow' => 'Москва',
            'spb' => 'Санкт-Петербург',
            'kazan' => 'Казань',
            'ekaterinburg' => 'Екатеринбург',
            'novosibirsk' => 'Новосибирск',
            'nizhny_novgorod' => 'Нижний Новгород',
            'samara' => 'Самара',
            'omsk' => 'Омск',
            'rostov_on_don' => 'Ростов-на-Дону',
            'ufa' => 'Уфа'
        ];

        return response()->json($locations);
    }
}
