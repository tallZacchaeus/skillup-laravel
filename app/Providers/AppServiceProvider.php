<?php

namespace App\Providers;

use App\Models\Catalog\Order;
use App\Observers\OrderObserver;
use App\Services\Cart\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Order::observe(OrderObserver::class);

        // Fold a guest's session cart into their account on login.
        Event::listen(Login::class, function (Login $event): void {
            if (Schema::hasTable('carts')) {
                app(CartService::class)->mergeGuestCartIntoUser($event->user);
            }
        });
    }
}
