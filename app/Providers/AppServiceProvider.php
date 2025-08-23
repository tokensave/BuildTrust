<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Привязываем интерфейсы репозиториев к их реализациям
        $this->app->bind(
            \App\Domain\Deal\Contracts\DealRepositoryInterface::class,
            \App\Infrastructure\Deal\Repositories\EloquentDealRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'auth.user.avatar_url' => fn() => optional(auth()->user())->avatar_url,
        ]);
    }
}
