<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\MiddlewareAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class MiddlewareAnalyzerTest extends TestCase
{
    private MiddlewareAnalyzer $analyzer;

    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'analyzers' => [
                'middleware' => ['enabled' => true, 'priority' => 60],
            ],
        ];

        $this->analyzer = new MiddlewareAnalyzer($this->config);
    }

    public function test_it_detects_views_in_middleware()
    {
        $tempDir = sys_get_temp_dir() . '/view_analyzer_test_Middleware_' . uniqid();
        mkdir($tempDir);

        $content = <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSubscribed
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->subscribed()) {
            // view helper usage
            return response(view('pages.subscribe'));
        }

        return $next($request);
    }
}
PHP;

        file_put_contents($tempDir . '/EnsureUserIsSubscribed.php', $content);

        $analyzer = new MiddlewareAnalyzer([
            'scan_paths' => [$tempDir],
            'analyzers' => ['middleware' => ['enabled' => true]],
        ]);

        $results = $analyzer->analyze();
        $viewNames = $results->pluck('viewName')->toArray();

        $this->assertContains('pages.subscribe', $viewNames);

        $ref = $results->first();
        $this->assertEquals('Middleware', $ref->context);
        $this->assertEquals('middleware', $ref->type);

        // Cleanup
        unlink($tempDir . '/EnsureUserIsSubscribed.php');
        rmdir($tempDir);
    }

    public function test_it_has_correct_metadata()
    {
        $this->assertEquals('Middleware Analyzer', $this->analyzer->getName());
        $this->assertTrue($this->analyzer->isEnabled());
        $this->assertEquals(60, $this->analyzer->getPriority());
    }

    public function test_it_respects_disabled_config()
    {
        $analyzer = new MiddlewareAnalyzer(['analyzers' => ['middleware' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());
    }

    public function test_it_skips_non_middleware_paths()
    {
        $analyzer = new MiddlewareAnalyzer(['scan_paths' => ['/non/existent/Path']]);
        $results = $analyzer->analyze();
        $this->assertCount(0, $results);
    }

    public function test_it_uses_default_app_path_when_config_is_empty()
    {
        $analyzer = new MiddlewareAnalyzer(['scan_paths' => []]);
        $results = $analyzer->analyze();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }

    public function test_it_skips_empty_files()
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Middleware_empty_' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/EmptyMiddleware.php';
        touch($file);

        $analyzer = new MiddlewareAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($file);
        rmdir($tempDir);
    }

    public function test_it_skips_paths_that_are_not_directories()
    {
        $tempFile = sys_get_temp_dir() . '/SomeMiddlewareFile.php';
        touch($tempFile);

        // Path exists but is a file, not a directory
        $analyzer = new MiddlewareAnalyzer(['scan_paths' => [$tempFile]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($tempFile);
    }
}
