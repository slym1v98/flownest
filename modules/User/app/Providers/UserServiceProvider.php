<?php

namespace Modules\User\Providers;

use App\Traits\HasModuleRegistrations;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class UserServiceProvider extends ServiceProvider
{
    use PathNamespace, HasModuleRegistrations;

    protected string $name = 'User';

    protected string $nameLower = 'user';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->name, '$MIGRATIONS_PATH$'));
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
