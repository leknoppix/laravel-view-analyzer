<?php

namespace LaravelViewAnalyzer\Tests\Unit\Reports;

use LaravelViewAnalyzer\Reports\HtmlReporter;
use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Tests\TestCase;

class HtmlReporterTest extends TestCase
{
    public function test_it_supports_html_format()
    {
        $reporter = new HtmlReporter();

        $this->assertTrue($reporter->supports('html'));
        $this->assertFalse($reporter->supports('json'));
    }

    public function test_it_generates_html_output()
    {
        $usedView = new ViewUsage('used.view', collect([]), 5, ['controller', 'blade']);

        $result = new AnalysisResult(
            totalViews: 10,
            usedViews: collect([$usedView]),
            unusedViews: collect(),
            dynamicViews: collect()
        );

        $reporter = new HtmlReporter();
        $html = $reporter->generate($result);

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Laravel View Analysis Report', $html);
        $this->assertStringContainsString('<div class="stat-value">10</div>', $html); // Total views
        $this->assertStringContainsString('used.view', $html);
        $this->assertStringContainsString('controller', $html);
        $this->assertStringContainsString('blade', $html);
    }
}
