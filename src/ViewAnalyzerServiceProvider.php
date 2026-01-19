<?php

namespace LaravelViewAnalyzer;

use Illuminate\Support\ServiceProvider;
use LaravelViewAnalyzer\Commands\ViewsAnalyzeCommand;
use LaravelViewAnalyzer\Commands\ViewsUnusedCommand;
use LaravelViewAnalyzer\Commands\ViewsUsedCommand;

class ViewAnalyzerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/view-analyzer.php',
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
                __DIR__.'/../config/view-analyzer.php' => config_path('view-analyzer.php'),
            ], 'view-analyzer-config');

            $this->commands([
                ViewsAnalyzeCommand::class,
                ViewsUsedCommand::class,
                ViewsUnusedCommand::class,
            ]);
        }
    }
}
