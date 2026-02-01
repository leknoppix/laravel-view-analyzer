<?php

namespace LaravelViewAnalyzer\Tests\Unit\Commands;

use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Tests\TestCase;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsUsedCommandTest extends TestCase
{
    public function test_it_lists_used_views()
    {
        $ref = new ViewReference('pages.home', 'ctrl.php', 10, 'ctx', 'controller');
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

        $this->artisan('views:used')
            ->expectsOutput('Finding used views...')
            ->expectsOutputToContain('Used Views (1 found)')
            ->expectsOutputToContain('pages.home')
            ->expectsOutputToContain('controller')
            ->assertExitCode(0);
    }

    public function test_it_filters_by_type()
    {
        $ref1 = new ViewReference('pages.home', 'ctrl.php', 10, 'ctx', 'controller');
        $usage1 = new ViewUsage('pages.home', collect([$ref1]), null, 1, ['controller']);

        $ref2 = new ViewReference('emails.welcome', 'mail.php', 20, 'ctx', 'mailable');
        $usage2 = new ViewUsage('emails.welcome', collect([$ref2]), null, 1, ['mailable']);

        $result = new AnalysisResult(
            totalViews: 2,
            usedViews: collect([$usage1, $usage2]),
            unusedViews: collect(),
            dynamicViews: collect()
        );

        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')->andReturn($result);
        });

        $this->artisan('views:used', ['--type' => 'mailable'])
            ->expectsOutputToContain('emails.welcome')
            ->doesntExpectOutputToContain('pages.home')
            ->assertExitCode(0);
    }
}
