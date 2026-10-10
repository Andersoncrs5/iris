<?php

namespace App\Providers;

use App\Bootstrap\PermissionBootstrapper;
use App\Bootstrap\SuperAdminBootstrapper;
use App\Models\User;
use App\Repository\Interfaces\ICategoryRepository;
use App\Repository\Interfaces\IUserRepository;
use App\Repository\UserRepository;
use App\Utils\Enums\SlugPermissionEnum;
use App\Utils\Snowflake\IdGeneratorInterface;
use App\Utils\Snowflake\SnowflakeIdGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(IdGeneratorInterface::class, function () {
            return new SnowflakeIdGenerator(config('app.snowflake_worker_id', 1));
        });

        $this->app->bind(IUserRepository::class, UserRepository::class);
        $this->app->bind(ICategoryRepository::class, \App\Repository\Provider\CategoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->boostarp();
        $this->registerGates();
    }

    private function boostarp() {
        if (Schema::hasTable('permissions') && Schema::hasTable('users')) {
            (new PermissionBootstrapper())->run();
            (new SuperAdminBootstrapper())->run();
        }
    }

    private function registerGates(): void
    {
        Gate::before(function (User $user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });
        
        foreach (SlugPermissionEnum::cases() as $permission) {
            Gate::define($permission->value, function (User $user) use ($permission) {
                return is_array($user->permissions) && in_array(
                    $permission->value, 
                    $user->permissions, 
                    true
                );
            });
        }
    }
}