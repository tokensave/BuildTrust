<?php

return [
    // API настройки
    'api_key' => env('GIGA_CHAT_API_KEY'),
    'client_secret' => env('GIGA_CHAT_CLIENT_SECRET'),
    'base_uri' => env('GIGA_CHAT_BASE_URI', 'https://gigachat.devices.sberbank.ru/api/v1'),
    'auth_uri' => env('GIGA_CHAT_AUTH_URI', 'https://ngw.devices.sberbank.ru:9443/api/v2'),
    'model' => env('GIGA_CHAT_MODEL', 'GigaChat:latest'),
    'scope' => env('GIGA_CHAT_SCOPE', 'GIGACHAT_API_PERS'),
    
    // Настройки для библиотеки edvardpotter/gigachat-php-sdk
    'sdk' => [
        'client_id' => env('GIGACHAT_CLIENT_ID'),
        'client_secret' => env('GIGACHAT_CLIENT_SECRET'), 
        'cert_path' => env('GIGACHAT_CERT_PATH', base_path('russiantrustedca.pem')),
        'scope' => env('GIGACHAT_SCOPE', 'GIGACHAT_API_PERS'),
        'use_ssl' => env('GIGACHAT_USE_SSL', true),
    ],

    // Timeout настройки
    'timeout' => env('GIGA_CHAT_TIMEOUT', 30),
    'connect_timeout' => env('GIGA_CHAT_CONNECT_TIMEOUT', 10),

    // SSL настройки
    'ssl_verify' => env('GIGA_CHAT_SSL_VERIFY', true),
    'ca_bundle_path' => env('GIGA_CHAT_CA_BUNDLE_PATH', storage_path('certificates/gigachat_ca_bundle.crt')),

    // Промпты для различных задач
    'prompts' => [
        'company_analysis' => [
            'system' => 'Ты - эксперт по анализу российских компаний. Твоя задача - провести детальный анализ компании на основе ее ИНН и названия, используя открытые источники.',
            'user_template' => 'Проведи детальный анализ компании по следующим данным:
- ИНН: {inn}
- Название: {company_name}

Проанализируй следующие аспекты:

1. ОСНОВНАЯ ИНФОРМАЦИЯ:
- Полное название и организационно-правовая форма
- Дата регистрации и текущий статус
- Основные виды деятельности (ОКВЭД)
- Юридический и фактический адрес
- Контактная информация (если доступна)

2. ФИНАНСОВОЕ СОСТОЯНИЕ:
- Уставный капитал
- Финансовые показатели за последние годы (если доступны)
- Налоговые задолженности
- Исполнительные производства

3. РЕПУТАЦИОННЫЙ АНАЛИЗ:
- Проверка в реестре недобросовестных поставщиков
- Проверка в реестре дисквалифицированных лиц
- Арбитражные дела
- Банкротство или процедуры банкротства
- Связи с другими компаниями

4. ДЕЛОВАЯ АКТИВНОСТЬ:
- Участие в госзакупках
- Лицензии и разрешения
- Сертификаты и награды
- Участие в СРО

5. РИСКИ:
- Массовый адрес регистрации
- Частая смена руководителей
- Связи с проблемными компаниями
- Налоговые и административные нарушения

Предоставь структурированный ответ в формате JSON со следующими полями:
{
    "summary": "Краткое резюме о компании (2-3 предложения)",
    "status": "active|inactive|liquidated|bankruptcy",
    "risk_level": "low|medium|high|critical",
    "key_points": ["массив", "ключевых", "моментов"],
    "financial_info": "Финансовая информация",
    "reputation_info": "Репутационная информация",
    "recommendations": "Рекомендации по работе с компанией",
    "last_updated": "дата последнего обновления информации"
}'
        ],

        'counterparty_check' => [
            'system' => 'Ты - эксперт по проверке контрагентов. Твоя задача - дать краткую, но информативную оценку надежности компании.',
            'user_template' => 'Проверь надежность контрагента:
- ИНН: {inn}
- Название: {company_name}

Дай краткий ответ (до 500 символов) с оценкой надежности и основными рисками или преимуществами.'
        ]
    ],

    // Настройки кэширования результатов
    'cache' => [
        'enabled' => env('GIGA_CHAT_CACHE_ENABLED', true),
        'ttl' => env('GIGA_CHAT_CACHE_TTL', 86400), // 24 часа
        'prefix' => 'gigachat_',
    ],

    // Настройки повторных попыток
    'retry' => [
        'max_attempts' => env('GIGA_CHAT_MAX_ATTEMPTS', 3),
        'delay' => env('GIGA_CHAT_RETRY_DELAY', 1000), // мс
    ],
];
