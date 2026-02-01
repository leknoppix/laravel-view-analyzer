<?php

namespace LaravelViewAnalyzer\Tests\Unit\Http;

use LaravelViewAnalyzer\Http\Controllers\ViewAnalyzerController;
use LaravelViewAnalyzer\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class ViewAnalyzerControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable web routes for testing
        Config::set('view-analyzer.web.enabled', true);

        // Register routes manually since we are testing the controller
        Route::get('/view-analyzer', [ViewAnalyzerController::class, 'index'])->name('view-analyzer.index');
    }

    public function test_it_renders_the_index_page()
    {
        $response = $this->get('/view-analyzer');

        $response->assertStatus(200);
        $response->assertViewIs('view-analyzer::index');
        $response->assertViewHasAll(['controllerMap', 'stats', 'unusedViews']);
    }
}
