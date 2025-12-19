<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        // Enable query caching for better performance
        if ($this->app->environment('production')) {
            // Cache configuration can be adjusted here
            config(['responsecache.cache_lifetime_in_seconds' => 3600]); // 1 hour
        }

        // Add indexes for better query performance
        // This will help with the search and filtering queries
        if ($this->app->environment('local', 'testing')) {
            DB::listen(function ($query) {
                // Log slow queries in development
                if ($query->time > 100) {
                    logger()->warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                    ]);
                }
            });
        }
    }
}
