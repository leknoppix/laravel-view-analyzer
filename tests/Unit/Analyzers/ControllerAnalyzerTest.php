<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\ControllerAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class ControllerAnalyzerTest extends TestCase
{
    private ControllerAnalyzer $analyzer;

    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'analyzers' => [
                'controller' => ['enabled' => true, 'priority' => 10],
            ],
        ];

        $this->analyzer = new ControllerAnalyzer($this->config);
    }

    public function test_it_detects_views_in_controllers()
    {
        $tempDir = sys_get_temp_dir() . '/view_analyzer_test_ctrl_' . uniqid();
        mkdir($tempDir);

        $content = <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;

class TestController
{
    public function index()
    {
        return view('pages.home');
    }

    public function show()
    {
        return View::make('pages.show');
    }

    public function dynamic()
    {
        $viewName = 'pages.dynamic';
        return view($viewName);
    }
}
PHP;

        file_put_contents($tempDir . '/TestController.php', $content);

        $analyzer = new ControllerAnalyzer([
            'scan_paths' => [$tempDir],
            'analyzers' => ['controller' => ['enabled' => true]],
        ]);

        $results = $analyzer->analyze();
        $viewNames = $results->pluck('viewName')->toArray();

        $this->assertContains('pages.home', $viewNames);
        $this->assertContains('pages.show', $viewNames);

        // Verify contexts
        $homeRef = $results->where('viewName', 'pages.home')->first();
        $this->assertStringContainsString('Controller::index', $homeRef->context);
        $this->assertEquals('controller', $homeRef->type);

        // Cleanup
        unlink($tempDir . '/TestController.php');
        rmdir($tempDir);
    }

    public function test_it_has_correct_metadata()
    {
        $this->assertEquals('Controller Analyzer', $this->analyzer->getName());
        $this->assertTrue($this->analyzer->isEnabled());
        $this->assertEquals(10, $this->analyzer->getPriority());
    }

    public function test_it_respects_disabled_config()
    {
        $analyzer = new ControllerAnalyzer(['analyzers' => ['controller' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());
    }

    public function test_it_skips_empty_files()
    {
        $tempDir = sys_get_temp_dir() . '/view_test_ctrl_empty_' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/EmptyController.php';
        touch($file);

        $analyzer = new ControllerAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($file);
        rmdir($tempDir);
    }

    public function test_it_uses_default_app_path_when_config_is_empty()
    {
        $analyzer = new ControllerAnalyzer(['scan_paths' => []]);
        $results = $analyzer->analyze();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }

    public function test_it_skips_paths_that_are_not_directories()
    {
        $tempFile = sys_get_temp_dir() . '/NotADirectory_ctrl_' . uniqid() . '.php';
        touch($tempFile);

        $analyzer = new ControllerAnalyzer(['scan_paths' => [$tempFile]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
        unlink($tempFile);
    }

    public function test_it_skips_unreadable_files()
    {
        $tempDir = sys_get_temp_dir() . '/view_test_ctrl_unreadable_' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/UnreadableController.php';
        touch($file);
        chmod($file, 0000);

        $analyzer = new ControllerAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        chmod($file, 0644);
        unlink($file);
        rmdir($tempDir);
    }
}
