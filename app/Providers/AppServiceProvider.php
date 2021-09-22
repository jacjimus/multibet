<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //bind - public path
        $this->bindPublicPath();

        //test units
        if ($this->app->runningUnitTests()) {
            Schema::defaultStringLength(191);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //setup environment
        if ($this->app->environment('local', 'testing') && class_exists(DuskServiceProvider::class)) {
            $this->app->register(DuskServiceProvider::class);
        }
    }

    /**
     * Bind application public path.
     *
     * @return void
     */
    public function bindPublicPath()
    {
        //save current public path
        $prev = public_path();

        //get config path
        $path = config('app.public');

        //prefer document root if available
        if (isset($_SERVER['DOCUMENT_ROOT']) && ($tmp = trim($_SERVER['DOCUMENT_ROOT']))) {
            $path = $tmp;
        }

        //remove trailing slash
        $path = rtrim($path, '/');

        //check path change - bind new path
        if ($path && $path != $prev) {
            $this->app->bind('path.public', function () use (&$path) {
                return $path;
            });

            //update filesystem links
            if (is_array($links = config('filesystems.links'))) {
                $tmp = [];
                foreach ($links as $key => $value) {
                    if (strpos($value, $prev) !== false) {
                        $value = str_replace($prev, $path, $value);
                    }
                    if (strpos($key, $prev) !== false) {
                        $key = str_replace($prev, $path, $key);
                    }
                    $tmp[$key] = $value;
                }
                config(['filesystems.links' => $tmp]);
            }
        }
    }
}
