<?php

namespace LaravelViewAnalyzer\Tests\Unit;

use Illuminate\Support\Facades\Route;
use LaravelViewAnalyzer\Tests\TestCase;
use LaravelViewAnalyzer\ViewAnalyzer;
use LaravelViewAnalyzer\ViewAnalyzerServiceProvider;

class ViewAnalyzerServiceProviderTest extends TestCase
{
    public function test_it_registers_the_analyzer_singleton()
    {
        $this->assertInstanceOf(ViewAnalyzer::class, $this->app->make(ViewAnalyzer::class));
    }

    public function test_it_merges_config()
    {
        $this->assertNotNull(config('view-analyzer'));
        $this->assertIsArray(config('view-analyzer.view_paths'));
    }

    public function test_it_registers_commands()
    {
        $commands = \Illuminate\Support\Facades\Artisan::all();

        $this->assertArrayHasKey('views:analyze', $commands);
        $this->assertArrayHasKey('views:used', $commands);
        $this->assertArrayHasKey('views:unused', $commands);
        $this->assertArrayHasKey('views:controllers', $commands);
    }

    public function test_it_registers_routes_when_enabled()
    {
        // Enforce enabled config
        config(['view-analyzer.web.enabled' => true]);
        config(['view-analyzer.web.path' => 'test-analyzer-path']);

        $provider = new ViewAnalyzerServiceProvider($this->app);
        $provider->boot();

        // Check if the route exists (it might have been registered with a different prefix)
        $routeFound = false;
        foreach (Route::getRoutes() as $route) {
            if ($route->getName() === 'view-analyzer.index') {
                $routeFound = true;
                break;
            }
        }

        $this->assertTrue($routeFound);
    }
}
