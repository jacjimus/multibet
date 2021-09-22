<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServicesProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //ModelService
        $this->app->singleton('ModelService', function ($app) {
            return new \App\Services\Models\ModelService();
        });

        //DatabaseService
        $this->app->singleton('DatabaseService', function ($app) {
            return new \App\Services\DatabaseService();
        });

        //SettingsService
        $this->app->singleton('SettingsService', function ($app) {
            return new \App\Services\SettingsService();
        });

        //RequestService
        $this->app->singleton('RequestService', function ($app) {
            return new \App\Services\RequestService();
        });

        //BackupService
        $this->app->singleton('BackupService', function ($app) {
            return new \App\Services\BackupService();
        });

        //ConsoleService
        $this->app->singleton('ConsoleService', function ($app) {
            return new \App\Services\ConsoleService();
        });

        //UserService
        $this->app->singleton('UserService', function ($app) {
            return new \App\Services\UserService();
        });

        //ViewService
        $this->app->singleton('ViewService', function ($app) {
            return new \App\Services\ViewService();
        });

        //UploadService
        $this->app->singleton('UploadService', function ($app) {
            return new \App\Services\UploadService();
        });
    }
}
