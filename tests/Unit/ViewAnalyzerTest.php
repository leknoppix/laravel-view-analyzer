<?php

namespace LaravelViewAnalyzer\Tests\Unit;

use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Tests\TestCase;
use LaravelViewAnalyzer\ViewAnalyzer;
use Mockery;

class ViewAnalyzerTest extends TestCase
{
    public function test_it_registers_default_analyzers()
    {
        $analyzer = new ViewAnalyzer();
        $reflection = new \ReflectionClass($analyzer);
        $property = $reflection->getProperty('analyzers');
        $property->setAccessible(true);
        $analyzers = $property->getValue($analyzer);

        // Check if all 9 default analyzers are registered
        $this->assertGreaterThanOrEqual(9, $analyzers->count());
        $analyzerClasses = $analyzers->map(fn ($a) => get_class($a))->toArray();

        $this->assertContains(\LaravelViewAnalyzer\Analyzers\ControllerAnalyzer::class, $analyzerClasses);
        $this->assertContains(\LaravelViewAnalyzer\Analyzers\BladeAnalyzer::class, $analyzerClasses);
        $this->assertContains(\LaravelViewAnalyzer\Analyzers\NotificationAnalyzer::class, $analyzerClasses);
    }

    public function test_it_analyzes_and_returns_result()
    {
        // Mock an analyzer
        $mockAnalyzer = Mockery::mock(AnalyzerInterface::class);
        $mockAnalyzer->shouldReceive('isEnabled')->andReturn(true);
        $mockAnalyzer->shouldReceive('getPriority')->andReturn(10);
        $mockAnalyzer->shouldReceive('analyze')->andReturn(collect([
            new ViewReference('mock.view', 'file.php', 1, 'context', 'mock'),
        ]));

        $analyzer = new ViewAnalyzer(['scan_paths' => []]);

        // Clear default analyzers and add mock
        $reflection = new \ReflectionClass($analyzer);
        $property = $reflection->getProperty('analyzers');
        $property->setAccessible(true);
        $property->setValue($analyzer, collect([$mockAnalyzer]));

        // Mock ViewFileScanner via partial mock of ViewAnalyzer or by mocking filesystem
        // Easier way: let it scan an empty temp dir
        $tempDir = sys_get_temp_dir() . '/views_' . uniqid();
        mkdir($tempDir);
        config(['view-analyzer.view_paths' => [$tempDir]]);

        $result = $analyzer->analyze();

        $this->assertEquals(1, $result->usedViews->count());
        $this->assertEquals('mock.view', $result->usedViews->first()->viewName);
        // It should have the full path from the mock registry if it existed,
        // but here the registry is empty or doesn't match 'mock.view'
        // Let's improve this test to verify the path resolution
    }

    public function test_it_resolves_file_paths_for_used_views()
    {
        $tempDir = sys_get_temp_dir() . '/views_' . uniqid();
        mkdir($tempDir);
        $viewFile = $tempDir . '/test.blade.php';
        touch($viewFile);

        config(['view-analyzer.view_paths' => [$tempDir]]);

        $mockAnalyzer = Mockery::mock(AnalyzerInterface::class);
        $mockAnalyzer->shouldReceive('isEnabled')->andReturn(true);
        $mockAnalyzer->shouldReceive('getPriority')->andReturn(10);
        $mockAnalyzer->shouldReceive('analyze')->andReturn(collect([
            new ViewReference('test', 'file.php', 1, 'context', 'mock'),
        ]));

        $analyzer = new ViewAnalyzer(['view_paths' => [$tempDir]]);

        $reflection = new \ReflectionClass($analyzer);
        $property = $reflection->getProperty('analyzers');
        $property->setAccessible(true);
        $property->setValue($analyzer, collect([$mockAnalyzer]));

        $result = $analyzer->analyze();

        $this->assertEquals(1, $result->usedViews->count());
        $this->assertEquals('test', $result->usedViews->first()->viewName);
        $this->assertEquals($viewFile, $result->usedViews->first()->filePath);

        unlink($viewFile);
        rmdir($tempDir);
    }

    public function test_it_identifies_unused_views()
    {
        // We need to mock the view registry to return some views
        $analyzer = Mockery::mock(ViewAnalyzer::class)->makePartial();
        $analyzer->__construct(['ignored_views' => []]);

        // Mock getViewRegistry protected method
        $analyzer->shouldAllowMockingProtectedMethods();
        $analyzer->shouldReceive('getViewRegistry')->andReturn(collect([
            'used.view' => '/path/used.blade.php',
            'unused.view' => '/path/unused.blade.php',
        ]));

        // Mock references
        $usedRef = new ViewReference('used.view', 'ctrl.php', 1, 'ctx', 'controller');

        // Mock analyze execution flow manually-ish or just test findUnusedViews directly
        // Testing findUnusedViews directly is cleaner
        $usedUsage = new \LaravelViewAnalyzer\Results\ViewUsage('used.view', collect([$usedRef]));

        $unused = $analyzer->findUnusedViews(
            collect([
                'used.view' => '/path/used.blade.php',
                'unused.view' => '/path/unused.blade.php',
            ]),
            collect([$usedUsage])
        );

        $this->assertCount(1, $unused);
        $this->assertEquals('unused.view', $unused->first()->viewName);
    }

    public function test_it_respects_ignored_views()
    {
        $config = ['ignored_views' => ['ignored.*']];
        $analyzer = new ViewAnalyzer($config);

        $viewRegistry = collect([
            'ignored.view' => '/path/ignored.blade.php',
            'normal.view' => '/path/normal.blade.php',
        ]);

        $usedViews = collect([]); // No views used

        $unused = $analyzer->findUnusedViews($viewRegistry, $usedViews);

        // 'ignored.view' should NOT be in unused views because it's ignored
        // 'normal.view' SHOULD be in unused views because it's not used and not ignored

        $this->assertCount(1, $unused);
        $this->assertEquals('normal.view', $unused->first()->viewName);
    }

    public function test_it_returns_statistics()
    {
        $analyzer = new ViewAnalyzer();
        $stats = $analyzer->getStatistics();

        $this->assertArrayHasKey('total_references', $stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertEquals(0, $stats['total_references']);
    }

    public function test_it_finds_used_views_directly()
    {
        $mockAnalyzer = Mockery::mock(AnalyzerInterface::class);
        $mockAnalyzer->shouldReceive('isEnabled')->andReturn(true);
        $mockAnalyzer->shouldReceive('getPriority')->andReturn(10);
        $mockAnalyzer->shouldReceive('analyze')->andReturn(collect([
            new ViewReference('test.view', 'file.php', 1, 'ctx', 'test'),
        ]));

        $analyzer = new ViewAnalyzer();
        $reflection = new \ReflectionClass($analyzer);
        $property = $reflection->getProperty('analyzers');
        $property->setAccessible(true);
        $property->setValue($analyzer, collect([$mockAnalyzer]));

        $used = $analyzer->findUsedViews();
        $this->assertCount(1, $used);
        $this->assertEquals('test.view', $used->first()->viewName);
    }
}
