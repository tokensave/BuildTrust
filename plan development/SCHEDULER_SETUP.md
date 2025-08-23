# Настройка планировщика для обновления AI анализов

## 1. Добавьте в `app/Console/Kernel.php` следующий код:

```php
protected function schedule(Schedule $schedule)
{
    // Обновление AI анализов компаний каждый день в 2:00
    $schedule->command('companies:update-ai-analysis')
             ->dailyAt('02:00')
             ->onOneServer() // Выполнять только на одном сервере
             ->runInBackground()
             ->emailOutputOnFailure(env('ADMIN_EMAIL'));

    // Альтернативно, можно запускать несколько раз в день
    // $schedule->command('companies:update-ai-analysis')->twiceDaily(2, 14);
}
```

## 2. Добавьте в crontab на сервере:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 3. Настройте очереди в `.env`:

```env
QUEUE_CONNECTION=database
# или используйте Redis для лучшей производительности:
# QUEUE_CONNECTION=redis
```

## 4. Запустите воркеры очередей:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

## 5. Для тестирования выполните команду вручную:

```bash
php artisan companies:update-ai-analysis
```

## 6. Мониторинг очередей:

```bash
# Просмотр состояния очередей
php artisan queue:monitor

# Просмотр неудавшихся задач
php artisan queue:failed

# Повтор неудавшихся задач
php artisan queue:retry all
```

## 7. Настройки GigaChat в `.env`:

```env
GIGA_CHAT_API_KEY=your_api_key_here
GIGA_CHAT_BASE_URI=https://gigachat.devices.sberbank.ru/api/v1
GIGA_CHAT_MODEL=GigaChat
GIGA_CHAT_TIMEOUT=30
GIGA_CHAT_CONNECT_TIMEOUT=10
GIGA_CHAT_CACHE_ENABLED=true
GIGA_CHAT_CACHE_TTL=86400
GIGA_CHAT_MAX_ATTEMPTS=3
GIGA_CHAT_RETRY_DELAY=1000
```
