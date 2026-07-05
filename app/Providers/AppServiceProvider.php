<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $request = request();
            $basePath = $request->getBasePath();
            if ($basePath !== '' && $basePath !== '/') {
                URL::forceRootUrl(rtrim($request->getSchemeAndHttpHost() . $basePath, '/'));
            }
        } catch (\Throwable $e) {
            // Sem request HTTP (ex.: alguns testes)
        }
    }
}
