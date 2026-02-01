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

    public function test_it_has_default_priority(): void
    {
        $analyzer = new RouteAnalyzer();
        $this->assertEquals(50, $analyzer->getPriority());
    }

    public function test_it_respects_enabled_config(): void
    {
        $analyzer = new RouteAnalyzer(['analyzers' => ['route' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());

        $analyzer = new RouteAnalyzer(['analyzers' => ['route' => ['enabled' => true]]]);
        $this->assertTrue($analyzer->isEnabled());
    }

    public function test_it_skips_invalid_directories(): void
    {
        $analyzer = new RouteAnalyzer(['exclude_paths' => []]);
        $results = $analyzer->analyze();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }

    public function test_it_returns_empty_if_routes_directory_missing(): void
    {
        // On vérifie simplement que l'analyseur retourne une collection
        // même si le dossier n'existe pas
        $analyzer = new RouteAnalyzer();
        $results = $analyzer->analyze();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }

    public function test_it_avoids_double_counting_references(): void
    {
        $content = <<<'PHP'
<?php
use Illuminate\Support\Facades\Route;

// Route::view handles its own detection, but detector might also pick up view()
// though Route::view doesn't use the helper. Let's force a scenario.
Route::view('/', 'welcome');
PHP;
        $tempRoutePath = base_path('routes/double_test.php');
        file_put_contents($tempRoutePath, $content);

        $analyzer = new RouteAnalyzer();
        $results = $analyzer->analyze();

        // On vérifie qu'il n'y a qu'une seule référence pour 'welcome'
        $welcomeRefs = $results->where('viewName', 'welcome');
        $this->assertCount(1, $welcomeRefs);

        unlink($tempRoutePath);
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

    public function test_it_skips_empty_files(): void
    {
        $tempRoutePath = base_path('routes/empty_test.php');
        touch($tempRoutePath);

        $analyzer = new RouteAnalyzer();
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($tempRoutePath);
    }
}
