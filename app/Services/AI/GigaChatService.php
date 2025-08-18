<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\AI;

// Используем GigaChat PHP SDK
use Edvardpotter\GigaChat\GigaChatOAuth;
use Edvardpotter\GigaChat\GigaChat;
use Edvardpotter\GigaChat\Type\Message;
use Edvardpotter\GigaChat\Type\Model;
use Edvardpotter\GigaChat\Type\AccessToken;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * GigaChat сервис на базе edvardpotter/gigachat-php-sdk
 * Решает все проблемы с 403 ошибками и Content-Type заголовками
 */
class GigaChatService
{
    private array $config;
    private ?GigaChat $gigaChatClient = null;
    private ?GigaChatOAuth $oauthClient = null;
    private ?AccessToken $accessToken = null;

    public function __construct()
    {
        $this->config = config('gigachat');
        
        // Проверяем наличие необходимых настроек SDK
        if (empty($this->config['sdk']['client_id'])) {
            throw new \InvalidArgumentException('GIGACHAT_CLIENT_ID не настроен в .env');
        }
        
        if (empty($this->config['sdk']['client_secret'])) {
            throw new \InvalidArgumentException('GIGACHAT_CLIENT_SECRET не настроен в .env');
        }

        Log::info('GigaChat SDK: Сервис инициализирован');
    }

    /**
     * Получает OAuth клиент для работы с токенами
     */
    private function getOAuthClient(): GigaChatOAuth
    {
        if ($this->oauthClient === null) {
            $clientId = $this->config['sdk']['client_id'];
            $clientSecret = $this->config['sdk']['client_secret'];
            $certPath = $this->config['sdk']['use_ssl'] ? $this->config['sdk']['cert_path'] : false;
            $scope = $this->config['sdk']['scope'];
            
            $this->oauthClient = new GigaChatOAuth($clientId, $clientSecret, $certPath, $scope);
            
            Log::info('GigaChat SDK: OAuth клиент создан', [
                'client_id' => substr($clientId, 0, 8) . '...',
                'use_ssl' => $this->config['sdk']['use_ssl'],
                'scope' => $scope
            ]);
        }

        return $this->oauthClient;
    }

    /**
     * Получает access token с кэшированием
     */
    private function getAccessToken(): AccessToken
    {
        $cacheKey = 'gigachat_sdk_access_token';
        
        // Проверяем локальный кэш
        if ($this->accessToken && !$this->accessToken->isExpired()) {
            return $this->accessToken;
        }
        
        // Проверяем кэш Laravel
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken && $cachedToken instanceof AccessToken && !$cachedToken->isExpired()) {
            $this->accessToken = $cachedToken;
            return $this->accessToken;
        }

        try {
            // Получаем новый токен через SDK
            $oauthClient = $this->getOAuthClient();
            $this->accessToken = $oauthClient->getAccessToken();

            // Кэшируем на 25 минут (токены живут 30 минут)
            Cache::put($cacheKey, $this->accessToken, now()->addMinutes(25));

            Log::info('GigaChat SDK: Получен новый access token');
            return $this->accessToken;

        } catch (\Exception $e) {
            Log::error('GigaChat SDK: Ошибка получения токена', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Не удалось получить токен доступа: ' . $e->getMessage());
        }
    }

    /**
     * Получает клиент GigaChat
     */
    private function getGigaChatClient(): GigaChat
    {
        if ($this->gigaChatClient === null) {
            $accessToken = $this->getAccessToken();
            $certPath = $this->config['sdk']['use_ssl'] ? $this->config['sdk']['cert_path'] : false;

            $this->gigaChatClient = new GigaChat($accessToken->getAccessToken(), $certPath);

            Log::info('GigaChat SDK: GigaChat клиент создан');
        }

        return $this->gigaChatClient;
    }

    /**
     * Анализирует компанию с помощью GigaChat SDK
     */
    public function analyzeCompany(string $inn, string $companyName, bool $forceUpdate = false): array
    {
        $cacheKey = $this->getCacheKey('company_analysis', $inn);

        // Проверяем кэш, если не требуется принудительное обновление
        if (!$forceUpdate && $this->config['cache']['enabled']) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::info('GigaChat SDK: Возвращены данные из кэша', ['inn' => $inn]);
                return $cached;
            }
        }

        try {
            // Строим сообщения на основе конфигурации промптов
            $messages = $this->buildMessages('company_analysis', [
                'inn' => $inn,
                'company_name' => $companyName
            ]);

            // Отправляем запрос через SDK (SDK сам добавляет Content-Type заголовки)
            $client = $this->getGigaChatClient();
            $completion = $client->chatCompletions(
                $messages,
                $this->config['model'] ?? Model::ID_GIGACHAT_LATEST,
                0.7, // temperature
                0.1, // top_p
                1,   // n
                false, // stream
                2048, // max_tokens
                1.0, // repetition_penalty
                0    // update_interval
            );

            // Парсим ответ
            $result = $this->parseCompanyAnalysisResponse($completion);

            // Сохраняем в кэш
            if ($this->config['cache']['enabled']) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
            }

            Log::info('GigaChat SDK: Успешно проанализирована компания', ['inn' => $inn]);
            return $result;

        } catch (\Exception $e) {
            Log::error('GigaChat SDK: Ошибка анализа компании', [
                'inn' => $inn,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Быстрая проверка контрагента через SDK
     */
    public function checkCounterparty(string $inn, string $companyName): array
    {
        try {
            // Строим сообщения
            $messages = $this->buildMessages('counterparty_check', [
                'inn' => $inn,
                'company_name' => $companyName
            ]);

            // Отправляем запрос через SDK
            $client = $this->getGigaChatClient();
            $completion = $client->chatCompletions(
                $messages,
                $this->config['model'] ?? Model::ID_GIGACHAT_LATEST,
                0.5, // более консервативная температура для точности
                0.1,
                1,
                false,
                1024, // меньше токенов для краткого ответа
                1.0
            );

            // Парсим ответ
            $result = $this->parseCounterpartyResponse($completion);

            Log::info('GigaChat SDK: Успешно проверен контрагент', ['inn' => $inn]);
            return $result;

        } catch (\Exception $e) {
            Log::error('GigaChat SDK: Ошибка проверки контрагента', [
                'inn' => $inn,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Строит массив сообщений на основе шаблона из конфигурации
     */
    private function buildMessages(string $promptType, array $variables): array
    {
        if (!isset($this->config['prompts'][$promptType])) {
            throw new \InvalidArgumentException("Промпт типа '$promptType' не найден в конфигурации");
        }

        $promptConfig = $this->config['prompts'][$promptType];
        $messages = [];

        // Системное сообщение
        if (isset($promptConfig['system'])) {
            $messages[] = new Message($promptConfig['system'], Message::ROLE_SYSTEM);
        }

        // Пользовательское сообщение с подстановкой переменных
        $userMessage = $promptConfig['user_template'];
        foreach ($variables as $key => $value) {
            $userMessage = str_replace("{{$key}}", $value, $userMessage);
        }
        $messages[] = new Message($userMessage, Message::ROLE_USER);

        return $messages;
    }

    /**
     * Парсит ответ анализа компании из объекта Completion (SDK)
     */
    private function parseCompanyAnalysisResponse($completion): array
    {
        $choices = $completion->getChoices();
        if (empty($choices)) {
            throw new \Exception('Пустой ответ от GigaChat API');
        }

        $content = $choices[0]->getMessage()->getContent();

        // Попробуем извлечь JSON из ответа
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            try {
                $jsonData = json_decode($matches[0], true, 512, JSON_THROW_ON_ERROR);
                if ($jsonData) {
                    return array_merge([
                        'raw_response' => $content,
                        'analysis_date' => now()->toISOString(),
                    ], $jsonData);
                }
            } catch (\JsonException $e) {
                Log::warning('GigaChat SDK: Не удалось парсить JSON из ответа', ['error' => $e->getMessage()]);
            }
        }

        // Если JSON не найден, возвращаем текстовый ответ
        return [
            'summary' => $content,
            'status' => 'unknown',
            'risk_level' => 'medium',
            'key_points' => [],
            'financial_info' => '',
            'reputation_info' => '',
            'recommendations' => $content,
            'raw_response' => $content,
            'analysis_date' => now()->toISOString(),
        ];
    }

    /**
     * Парсит ответ проверки контрагента из объекта Completion (SDK)
     */
    private function parseCounterpartyResponse($completion): array
    {
        $choices = $completion->getChoices();
        if (empty($choices)) {
            throw new \Exception('Пустой ответ от GigaChat API');
        }

        $content = $choices[0]->getMessage()->getContent();

        return [
            'description' => $content,
            'check_date' => now()->toISOString(),
        ];
    }

    /**
     * Генерирует ключ для кэширования
     */
    private function getCacheKey(string $type, string $identifier): string
    {
        return $this->config['cache']['prefix'] . $type . '_' . md5($identifier);
    }
}
