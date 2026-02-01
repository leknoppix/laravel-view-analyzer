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
}
