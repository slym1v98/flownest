<?php

namespace Modules\Dashboard\Providers;

use App\Traits\HasModuleRegistrations;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class DashboardServiceProvider extends ServiceProvider
{
    use PathNamespace, HasModuleRegistrations;

    protected string $name = 'Dashboard';

    protected string $nameLower = 'dashboard';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
