<?php

namespace LaravelViewAnalyzer\Tests\Unit\Commands;

use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Tests\TestCase;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsAnalyzeCommandTest extends TestCase
{
    public function test_it_runs_analyze_command()
    {
        // Mock the result
        $result = new AnalysisResult(
            totalViews: 5,
            usedViews: collect([1, 2, 3]),
            unusedViews: collect([4, 5]),
            dynamicViews: collect(),
            statistics: [],
            warnings: []
        );

        // Use Laravel's mock helper
        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')
                ->once()
                ->andReturn($result);
        });

        $this->artisan('views:analyze')
            ->expectsOutput('Analyzing views...')
            ->expectsOutputToContain('Laravel View Analyzer - Analysis Report')
            // Note: The output contains leading spaces
            ->expectsOutputToContain('Total Views Found: 5')
            ->expectsOutputToContain('Used Views: 3')
            ->expectsOutputToContain('Unused Views: 2')
            ->assertExitCode(0);
    }

    public function test_it_exports_to_json_file()
    {
        $outputFile = sys_get_temp_dir() . '/report.json';

        // Mock Result
        $result = new AnalysisResult(
            totalViews: 5,
            usedViews: collect(),
            unusedViews: collect(),
            dynamicViews: collect()
        );

        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')->andReturn($result);
        });

        $this->artisan('views:analyze', [
            '--format' => 'json',
            '--output' => $outputFile,
        ])
            ->expectsOutput('Analyzing views...')
            ->expectsOutput("Report saved to: {$outputFile}")
            ->assertExitCode(0);

        $this->assertFileExists($outputFile);
        $content = file_get_contents($outputFile);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertEquals(5, $data['total_views']);

        unlink($outputFile);
    }

    public function test_it_exports_to_csv_format()
    {
        // Mock Result with some data for CSV
        $result = new AnalysisResult(
            totalViews: 1,
            usedViews: collect(),
            unusedViews: collect(),
            dynamicViews: collect()
        );

        $this->mock(ViewAnalyzer::class, function ($mock) use ($result) {
            $mock->shouldReceive('analyze')->andReturn($result);
        });

        $this->artisan('views:analyze', ['--format' => 'csv'])
            ->expectsOutputToContain('"View Name",Status,"Reference Count","File Path",Types') // CSV Header
            ->assertExitCode(0);
    }
}
