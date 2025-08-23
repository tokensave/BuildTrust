<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Deal\UseCases\ChangeDealStatusUseCase;
use App\Application\Deal\UseCases\CreateDealUseCase;
use App\Application\Deal\UseCases\GetUserDealsUseCase;
use App\Domain\Deal\Contracts\DealRepositoryInterface;
use App\Domain\Deal\Events\DealWasCreated;
use App\Infrastructure\Deal\Listeners\SendNewDealToBlockchain;
use App\Infrastructure\Deal\Repositories\EloquentDealRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider для DDD архитектуры сделок
 * 
 * Регистрирует все биндинги для работы с доменом Deal:
 * - Repository интерфейсы → Infrastructure реализации
 * - Use Cases для Application слоя
 * - Event Listeners (в будущем)
 */
class DealServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов в контейнере
     */
    public function register(): void
    {
        // === REPOSITORY LAYER ===
        
        // Биндим интерфейс репозитория к его Eloquent реализации
        $this->app->bind(
            DealRepositoryInterface::class,
            EloquentDealRepository::class
        );

        // === APPLICATION LAYER ===
        
        // Use Cases регистрируются как синглтоны для производительности
        $this->app->singleton(CreateDealUseCase::class, function ($app) {
            return new CreateDealUseCase(
                dealRepository: $app->make(DealRepositoryInterface::class)
            );
        });

        $this->app->singleton(ChangeDealStatusUseCase::class, function ($app) {
            return new ChangeDealStatusUseCase(
                dealRepository: $app->make(DealRepositoryInterface::class)
            );
        });

        $this->app->singleton(GetUserDealsUseCase::class, function ($app) {
            return new GetUserDealsUseCase(
                dealRepository: $app->make(DealRepositoryInterface::class)
            );
        });
    }

    /**
     * Загрузка сервисов после регистрации
     */
    public function boot(): void
    {
        // Регистрируем Domain Event Listeners
        Event::listen(DealWasCreated::class, SendNewDealToBlockchain::class);
        
        // TODO: Добавить другие listeners по мере необходимости
        // Event::listen(DealStatusWasChanged::class, SendStatusUpdateToBlockchain::class);
        // Event::listen(DealWasCreated::class, SendDealNotification::class);
    }

    /**
     * Сервисы, которые предоставляет этот Provider
     */
    public function provides(): array
    {
        return [
            DealRepositoryInterface::class,
            CreateDealUseCase::class,
            ChangeDealStatusUseCase::class,
            GetUserDealsUseCase::class,
        ];
    }
}
