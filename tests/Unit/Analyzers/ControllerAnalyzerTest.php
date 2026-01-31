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
}
