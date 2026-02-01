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

    public function test_it_filters_by_min_references()
    {
        $ref1 = new ViewReference('v1', 'c1.php', 1, 'ctx', 'blade');
        $usage1 = new ViewUsage('v1', collect([$ref1]), null, 1, ['blade']);

        $ref2 = new ViewReference('v2', 'c2.php', 1, 'ctx', 'blade');
        $usage2 = new ViewUsage('v2', collect([$ref2, $ref2]), null, 2, ['blade']);

        $result = new AnalysisResult(2, collect([$usage1, $usage2]), collect(), collect());
        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        $this->artisan('views:used', ['--min-references' => 2])
            ->expectsOutputToContain('v2')
            ->doesntExpectOutputToContain('v1')
            ->assertExitCode(0);
    }

    public function test_it_sorts_by_count()
    {
        $u1 = new ViewUsage('v1', collect([new ViewReference('v1', 'f.php', 1, 'c', 'b')]), null, 1);
        $u2 = new ViewUsage('v2', collect([new ViewReference('v2', 'f.php', 1, 'c', 'b'), new ViewReference('v2', 'f2.php', 1, 'c', 'b')]), null, 2);

        $result = new AnalysisResult(2, collect([$u1, $u2]), collect(), collect());
        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        // Sorting by count should put v2 (2 refs) before v1 (1 ref)
        $this->artisan('views:used', ['--sort' => 'count'])
            ->assertExitCode(0);
    }

    public function test_it_shows_locations()
    {
        $ref = new ViewReference('v1', '/path/to/source.php', 42, 'Context::method', 'controller');
        $usage = new ViewUsage('v1', collect([$ref]), null, 1, ['controller']);

        $result = new AnalysisResult(1, collect([$usage]), collect(), collect());
        $this->mock(ViewAnalyzer::class, fn ($mock) => $mock->shouldReceive('analyze')->andReturn($result));

        $this->artisan('views:used', ['--show-locations' => true])
            ->expectsOutputToContain('/path/to/source.php:42 (Context::method)')
            ->assertExitCode(0);
    }
}
