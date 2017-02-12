<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
            $func_or_obj          = require_once $pathname;
            $filename             = pathinfo($pathname, PATHINFO_FILENAME);
            $app_func_or_obj_name = camel_case($filename);

            $app->addHelper($app_func_or_obj_name, $func_or_obj);
        }
    }
}
