<?php

namespace Kaishiyoku\CreateUser;

use Illuminate\Support\ServiceProvider;

class CreateUserServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/createuser.php' => config_path('createuser.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/createuser.php', 'createuser'
        );

        $this->commands([
            Commands\ListUsers::class,
            Commands\CreateUser::class,
            Commands\RemoveUser::class,
        ]);
    }
}
