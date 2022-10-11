<?php

namespace Yarm\Servicetools;

use Illuminate\Support\ServiceProvider;

class ServicetoolsServiceProvider extends ServiceProvider{

    public function boot()
    {

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/views','servicetools');
        //$this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/config/ServiceTools.php','servicetools');
        $this->publishes([
            //__DIR__ . '/config/bookshelf.php' => config_path('bookshelf.php'),
            //__DIR__ . '/views' => resource_path('views/vendor/adminkeywords'),
            // Assets
            __DIR__ . '/js' => resource_path('js/vendor'),
        ],'servicetools');


        //after every update
        //run php artisan vendor:publish --provider="Yarm\Servicetools\ServicetoolsServiceProvider" --tag="servicetools" --force

    }

    public function register()
    {

    }

}
