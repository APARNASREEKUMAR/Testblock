<?php
namespace App\Repositories;

use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            'App\Repositories\UserRepositoryInterface',
            'App\Repositories\UserRepository'
        );
         $this->app->bind(
            'App\Repositories\UserAdtnlInfoRepositoryInterface',
            'App\Repositories\UserAdtnlInfoRepository'
        );
         $this->app->bind(
            'App\Repositories\UserActivityRepositoryInterface',
            'App\Repositories\UserActivityRepository'
        );
    }
}