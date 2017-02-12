<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use stdClass;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $app = app();

        foreach (glob(app_path() . '/Helpers/*.php') as $pathname) {
            $callback = require_once $pathname;

            $filename      = pathinfo($pathname, PATHINFO_FILENAME);
            $app_func_name = camel_case($filename);

            $app->addHelper($app_func_name, $callback);
        }
    }
}
