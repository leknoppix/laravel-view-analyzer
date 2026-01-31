<?php

namespace LaravelViewAnalyzer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaravelViewAnalyzer\Commands\ViewsAnalyzeCommand;
use LaravelViewAnalyzer\Commands\ViewsControllersCommand;
use LaravelViewAnalyzer\Commands\ViewsUnusedCommand;
use LaravelViewAnalyzer\Commands\ViewsUsedCommand;

class ViewAnalyzerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/view-analyzer.php',
            'view-analyzer'
        );

        $this->app->singleton(ViewAnalyzer::class, function ($app) {
            return new ViewAnalyzer(config('view-analyzer'));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/view-analyzer.php' => config_path('view-analyzer.php'),
            ], 'view-analyzer-config');

            $this->commands([
                ViewsAnalyzeCommand::class,
                ViewsUsedCommand::class,
                ViewsUnusedCommand::class,
                ViewsControllersCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'view-analyzer');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/view-analyzer'),
        ], 'view-analyzer-views');

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        if (config('view-analyzer.web.enabled', false)) {
            Route::group([
                'prefix' => config('view-analyzer.web.path', 'admin/viewpackage'),
                'middleware' => config('view-analyzer.web.middleware', ['web']),
            ], function () {
                Route::get('/', [\LaravelViewAnalyzer\Http\Controllers\ViewAnalyzerController::class, 'index'])
                    ->name('view-analyzer.index');
            });
        }
    }
}
