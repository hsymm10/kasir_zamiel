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
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $settings = \App\Models\Setting::pluck('value', 'key')->all();
            view()->share('settings', $settings);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
            $lowStockCount = \App\Models\Product::where('stock', '<=', 5)->count();
            view()->share('lowStockCount', $lowStockCount);
        }
    }
}
