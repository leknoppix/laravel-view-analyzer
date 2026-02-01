<?php

namespace LaravelViewAnalyzer\Tests\Unit\Commands;

use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\UnusedView;
use LaravelViewAnalyzer\Tests\TestCase;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsUnusedCommandTest extends TestCase
{
    public function test_it_lists_unused_views()
    {
        $unusedView = new UnusedView(
            viewName: 'unused.view',
            filePath: '/path/to/unused.blade.php',
            fileSize: 1024,
            lastModified: now()
        );

        $result = new AnalysisResult(
            totalViews: 1,
            usedViews: collect(),
            unusedViews: collect([$unusedView]),
            dynamicViews: collect()
        );

        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')->once()->andReturn($result);
        });

        $this->artisan('views:unused')
            ->expectsOutput('Finding unused views...')
            ->expectsOutputToContain('Unused Views (1 found)')
            ->expectsOutputToContain('unused.view')
            ->expectsOutputToContain('/path/to/unused.blade.php')
            ->assertExitCode(0);
    }

    public function test_it_shows_no_unused_views_message()
    {
        $result = new AnalysisResult(
            totalViews: 5,
            usedViews: collect([1, 2, 3, 4, 5]),
            unusedViews: collect(),
            dynamicViews: collect()
        );

        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')->andReturn($result);
        });

        $this->artisan('views:unused')
            ->expectsOutput('Finding unused views...')
            ->expectsOutputToContain('No unused views found!')
            ->assertExitCode(0);
    }

    public function test_it_filters_by_path()
    {
        $view1 = new UnusedView('admin.dash', '/path/to/views/admin/dash.blade.php', 1024, now());
        $view2 = new UnusedView('home', '/path/to/views/home.blade.php', 2048, now());
        $result = new AnalysisResult(2, collect(), collect([$view1, $view2]), collect());

        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        $this->artisan('views:unused', ['--path' => 'admin'])
            ->expectsOutputToContain('admin.dash')
            ->doesntExpectOutputToContain('home')
            ->assertExitCode(0);
    }

    public function test_it_shows_file_sizes()
    {
        $view = new UnusedView('test', '/path/test.blade.php', 1024, now());
        $result = new AnalysisResult(1, collect(), collect([$view]), collect());

        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        $this->artisan('views:unused', ['--size' => true])
            ->expectsOutputToContain('Size: 1 KB')
            ->assertExitCode(0);
    }

    public function test_it_suggests_delete_commands()
    {
        $view = new UnusedView('test', '/path/test.blade.php', 1024, now());
        $result = new AnalysisResult(1, collect(), collect([$view]), collect());

        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        $this->artisan('views:unused', ['--suggest-delete' => true])
            ->expectsOutputToContain('rm "/path/test.blade.php"')
            ->assertExitCode(0);
    }
}
