<?php

namespace App\Providers;

use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\OrderEloquentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\PaymentEloquentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\ProductEloquentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\UserEloquentRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderEloquentRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentEloquentRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductEloquentRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserEloquentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
