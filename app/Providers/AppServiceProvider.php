<?php

namespace App\Providers;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Payment\Events\PaymentFailed;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Product\Events\ProductStockReduced;
use App\Domain\Product\Events\ProductStockRestored;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Listeners\ReduceProductStock;
use App\Infrastructure\Listeners\RestoreProductStock;
use App\Infrastructure\Listeners\SendOrderPaidNotification;
use App\Infrastructure\Listeners\WriteOrderLog;
use App\Infrastructure\Listeners\WritePaymentLog;
use App\Infrastructure\Persistence\Eloquent\Repositories\OrderEloquentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\PaymentEloquentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\ProductEloquentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\UserEloquentRepository;
use Illuminate\Support\Facades\Event;
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
        Event::listen(OrderCreated::class, WriteOrderLog::class);
        Event::listen(OrderCancelled::class, WriteOrderLog::class);
        Event::listen(OrderPaid::class, WriteOrderLog::class);
        Event::listen(OrderPaid::class, SendOrderPaidNotification::class);
        Event::listen(PaymentFailed::class, WritePaymentLog::class);
        Event::listen(ProductStockReduced::class, ReduceProductStock::class);
        Event::listen(ProductStockRestored::class, RestoreProductStock::class);
    }
}
