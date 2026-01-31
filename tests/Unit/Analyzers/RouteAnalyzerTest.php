<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\RouteAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class RouteAnalyzerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! is_dir(base_path('routes'))) {
            mkdir(base_path('routes'), 0777, true);
        }
    }

    public function test_it_has_correct_name(): void
    {
        $analyzer = new RouteAnalyzer();
        $this->assertEquals('Route Analyzer', $analyzer->getName());
    }

    public function test_it_detects_route_view_shorthand(): void
    {
        $content = <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('/about', 'pages.about');
PHP;

        $tempRoutePath = base_path('routes/web_test.php');
        file_put_contents($tempRoutePath, $content);

        $analyzer = new RouteAnalyzer();

        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();

        $this->assertContains('welcome', $viewNames);
        $this->assertContains('pages.about', $viewNames);

        unlink($tempRoutePath);
    }

    public function test_it_detects_view_helper_in_closures(): void
    {
        $content = <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

Route::get('/contact', function () {
    return view('pages.contact');
});

Route::get('/profile', fn() => view('users.profile'));
PHP;

        $tempRoutePath = base_path('routes/api_test.php');
        file_put_contents($tempRoutePath, $content);

        $analyzer = new RouteAnalyzer();

        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();

        $this->assertContains('pages.contact', $viewNames);
        $this->assertContains('users.profile', $viewNames);

        unlink($tempRoutePath);
    }
}
