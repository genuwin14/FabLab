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
        \Illuminate\Support\Facades\View::composer('customer.partials.sidebar', function ($view) {
            $count = 0;
            if (\Illuminate\Support\Facades\Auth::check()) {
                $count = \App\Models\Order::where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->whereIn('status', ['pending', 'processing'])
                    ->count();
            }
            $view->with('inProgressCount', $count);
        });
    }
}
