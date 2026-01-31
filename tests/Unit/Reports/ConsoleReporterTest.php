<?php

namespace LaravelViewAnalyzer\Tests\Unit\Reports;

use LaravelViewAnalyzer\Reports\ConsoleReporter;
use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Tests\TestCase;

class ConsoleReporterTest extends TestCase
{
    public function test_it_supports_console_formats()
    {
        $reporter = new ConsoleReporter();

        $this->assertTrue($reporter->supports('console'));
        $this->assertTrue($reporter->supports('table'));
        $this->assertTrue($reporter->supports('text'));
        $this->assertFalse($reporter->supports('json'));
        $this->assertFalse($reporter->supports('html'));
    }

    public function test_it_generates_formatted_output()
    {
        $result = new AnalysisResult(
            totalViews: 10,
            usedViews: collect([1, 2, 3, 4, 5, 6, 7]), // 7 used
            unusedViews: collect([8, 9, 10]), // 3 unused
            dynamicViews: collect(),
            statistics: [
                'total_references' => 15,
                'by_type' => ['controller' => 10, 'blade' => 5]
            ],
            warnings: ['Warning: something is wrong']
        );

        $reporter = new ConsoleReporter();
        $output = $reporter->generate($result);

        $this->assertStringContainsString('Laravel View Analyzer - Analysis Report', $output);
        $this->assertStringContainsString('Total Views Found: 10', $output);
        $this->assertStringContainsString('Used Views: 7', $output);
        $this->assertStringContainsString('Unused Views: 3', $output);

        // Check statistics formatting
        $this->assertStringContainsString('Total references: 15', $output);
        $this->assertStringContainsString('By type: {"controller":10,"blade":5}', $output);

        // Check warnings
        $this->assertStringContainsString('Warnings:', $output);
        $this->assertStringContainsString('- Warning: something is wrong', $output);
    }
}
