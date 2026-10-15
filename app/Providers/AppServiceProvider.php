<?php

namespace App\Providers;

use App\Models\ConfiguracaoSistema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('*', function ($view) {
            try {
                $view->with('brand', ConfiguracaoSistema::atual());
            } catch (\Throwable $e) {
                //
            }
        });

        if (app()->environment('production') || str_starts_with(config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

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
            // Sem request HTTP
        }
    }
}
