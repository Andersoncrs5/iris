<?php

namespace App\Providers;

use App\Repository\CategoryRepository;
use App\Repository\Interfaces\ICategoryRepository;
use App\Repository\Interfaces\IUserRepository;
use App\Repository\UserRepository;
use App\Utils\Snowflake\IdGeneratorInterface;
use App\Utils\Snowflake\SnowflakeIdGenerator;
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
        $this->app->bind(ICategoryRepository::class, CategoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}