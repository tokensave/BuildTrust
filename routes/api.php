<?php


use App\Http\Controllers\Api\FilterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API для получения данных аутентифицированного пользователя
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API для фильтров объявлений
Route::prefix('filters')->group(function () {
    Route::get('types', [FilterController::class, 'getTypes'])->name('api.filters.types');
    Route::get('categories', [FilterController::class, 'getCategories'])->name('api.filters.categories');
    Route::get('categories-by-type', [FilterController::class, 'getCategoriesByType'])->name('api.filters.categories-by-type');
    Route::get('subcategories', [FilterController::class, 'getSubcategories'])->name('api.filters.subcategories');
    Route::get('structure', [FilterController::class, 'getAllStructure'])->name('api.filters.structure');
    Route::get('locations', [FilterController::class, 'getPopularLocations'])->name('api.filters.locations');
});
