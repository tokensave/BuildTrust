<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\AI\GigaChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AiController extends Controller
{
    public function __construct(
        private readonly GigaChatService $gigaChatService
    ) {
//        $this->middleware('auth:sanctum');
    }

    /**
     * Поиск и анализ компании через AI
     */
    public function searchCompany(Request $request): JsonResponse
    {
        try {
            $inn = $request->input('inn');
            $companyName = $request->input('company_name');
            $forceUpdate = $request->boolean('force_update', false);

            Log::info('AI: Запрос анализа компании', [
                'inn' => $inn,
                'company_name' => $companyName,
                'force_update' => $forceUpdate,
                'user_id' => $request->user()?->id
            ]);
            // Сначала пытаемся найти компанию в базе данных
            $company = Company::where('inn', $inn)->first();

            // Если компания найдена и у неё есть свежий AI анализ, возвращаем его
            if ($company && !$forceUpdate && $this->hasRecentAnalysis($company)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'type' => 'cached',
                        'analysis' => json_decode($company->ai_analysis, true) ?? [],
                        'description' => $company->ai_description,
                        'last_check' => $company->ai_last_check,
                        'status' => $company->ai_status
                    ]
                ]);
            }
            // Запрашиваем анализ у AI
            $analysisResult = $this->gigaChatService->analyzeCompany($inn, $companyName, $forceUpdate);

            // Обновляем или создаем запись компании с результатами анализа
            if ($company) {
                $this->updateCompanyAnalysis($company, $analysisResult);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'fresh',
                    'analysis' => $analysisResult,
                    'description' => $analysisResult['summary'] ?? 'Анализ не содержит краткого описания',
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('AI: Ошибка анализа компании', [
                'inn' => $request->input('inn'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при анализе компании: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Быстрая проверка контрагента
     */
    public function checkCounterparty(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'inn' => ['required', 'string', 'min:10', 'max:12', 'regex:/^\d+$/'],
                'company_name' => ['required', 'string', 'max:500'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $inn = $request->input('inn');
            $companyName = $request->input('company_name');

            Log::info('AI: Запрос проверки контрагента', [
                'inn' => $inn,
                'company_name' => $companyName,
                'user_id' => $request->user()?->id
            ]);

            $result = $this->gigaChatService->checkCounterparty($inn, $companyName);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('AI: Ошибка проверки контрагента', [
                'inn' => $request->input('inn'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при проверке контрагента: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверяет, есть ли у компании свежий AI анализ (не старше суток)
     */
    private function hasRecentAnalysis(Company $company): bool
    {
        if (!$company->ai_last_check || !$company->ai_analysis) {
            return false;
        }

        $lastCheck = $company->ai_last_check;
        $dayAgo = now()->subDay();

        return $lastCheck->isAfter($dayAgo) && $company->ai_status === 'completed';
    }

    /**
     * Обновляет данные анализа компании
     */
    private function updateCompanyAnalysis(Company $company, array $analysisResult): void
    {
        $company->update([
            'ai_analysis' => json_encode($analysisResult),
            'ai_description' => $analysisResult['summary'] ?? null,
            'ai_last_check' => now(),
            'ai_status' => 'completed'
        ]);
    }
}
