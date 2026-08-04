<?php

namespace App\Providers;

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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Stock alerts cover everything the shop counts, not just products.
        \App\Models\Product::observe(\App\Observers\StockLevelObserver::class);
        \App\Models\RawMaterial::observe(\App\Observers\StockLevelObserver::class);
        \App\Models\Texture::observe(\App\Observers\StockLevelObserver::class);

        \Illuminate\Support\Facades\View::composer('customer.partials.sidebar', function ($view) {
            $count = 0;
            $latestActiveOrder = null;
            if (\Illuminate\Support\Facades\Auth::check()) {
                $userId = \Illuminate\Support\Facades\Auth::id();
                $activeStatuses = ['pending', 'processing'];

                $count = \App\Models\Order::where('user_id', $userId)
                    ->whereIn('status', $activeStatuses)
                    ->count();

                $latestActiveOrder = \App\Models\Order::with(['orderItems.product', 'orderItems.customDesign'])
                    ->where('user_id', $userId)
                    ->whereIn('status', $activeStatuses)
                    ->latest('created_at')
                    ->first();
            }
            $view->with('inProgressCount', $count);
            $view->with('latestActiveOrder', $latestActiveOrder);
        });
    }
}
