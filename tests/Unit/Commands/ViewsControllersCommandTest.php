<?php

namespace LaravelViewAnalyzer\Tests\Unit\Commands;

use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Tests\TestCase;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsControllersCommandTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFile = sys_get_temp_dir() . '/view_analyzer_test_' . uniqid() . '.json';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
        parent::tearDown();
    }

    public function test_it_lists_controller_views()
    {
        $ref = new ViewReference('pages.home', app_path('Http/Controllers/HomeController.php'), 10, 'HomeController::index', 'controller');
        $usage = new ViewUsage('pages.home', collect([$ref]), null, 1, ['controller']);

        $result = new AnalysisResult(
            totalViews: 1,
            usedViews: collect([$usage]),
            unusedViews: collect(),
            dynamicViews: collect()
        );

        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')->once()->andReturn($result);
        });

        $this->artisan('views:controllers', [
            '--format' => 'json',
            '--output' => $this->tempFile,
        ])->assertExitCode(0);

        $this->assertFileExists($this->tempFile);
        $content = file_get_contents($this->tempFile);

        $this->assertStringContainsString('"controller": "HomeController"', $content);
        $this->assertStringContainsString('"action": "index"', $content);
        $this->assertStringContainsString('"pages.home"', $content);
    }

    public function test_it_filters_by_controller_name()
    {
        $ref1 = new ViewReference('pages.home', app_path('Http/Controllers/HomeController.php'), 10, 'HomeController::index', 'controller');
        $usage1 = new ViewUsage('pages.home', collect([$ref1]), null, 1, ['controller']);

        $ref2 = new ViewReference('admin.dash', app_path('Http/Controllers/AdminController.php'), 20, 'AdminController::index', 'controller');
        $usage2 = new ViewUsage('admin.dash', collect([$ref2]), null, 1, ['controller']);

        $result = new AnalysisResult(
            totalViews: 2,
            usedViews: collect([$usage1, $usage2]),
            unusedViews: collect(),
            dynamicViews: collect()
        );

        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')->andReturn($result);
        });

        $this->artisan('views:controllers', [
            '--controller' => 'Admin',
            '--format' => 'json',
            '--output' => $this->tempFile,
        ])->assertExitCode(0);

        $this->assertFileExists($this->tempFile);
        $content = file_get_contents($this->tempFile);

        $this->assertStringContainsString('"controller": "AdminController"', $content);
        $this->assertStringNotContainsString('"controller": "HomeController"', $content);
    }

    public function test_it_supports_different_formats()
    {
        $ref = new ViewReference('pages.home', 'HomeController.php', 10, 'index', 'controller');
        $usage = new ViewUsage('pages.home', collect([$ref]), null, 1, ['controller']);
        $result = new AnalysisResult(1, collect([$usage]), collect(), collect());

        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        // Test Table (default)
        $this->artisan('views:controllers')->assertExitCode(0);

        // Test CSV
        $this->artisan('views:controllers', ['--format' => 'csv'])->assertExitCode(0);

        // Test Tree
        $this->artisan('views:controllers', ['--format' => 'tree'])->assertExitCode(0);
    }

    public function test_it_handles_empty_controllers_with_option()
    {
        $result = new AnalysisResult(0, collect(), collect(), collect());
        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        $this->artisan('views:controllers', ['--include-empty' => true])
            ->expectsOutputToContain('Total controllers: 0')
            ->assertExitCode(0);
    }

    public function test_it_groups_by_namespace()
    {
        $ref = new ViewReference('pages.home', 'HomeController.php', 10, 'index', 'controller');
        $usage = new ViewUsage('pages.home', collect([$ref]), null, 1, ['controller']);
        $result = new AnalysisResult(1, collect([$usage]), collect(), collect());

        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        $this->artisan('views:controllers', ['--group-by-namespace' => true])
            ->assertExitCode(0);
    }
}
