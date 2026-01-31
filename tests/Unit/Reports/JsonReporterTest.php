<?php

namespace LaravelViewAnalyzer\Tests\Unit\Reports;

use LaravelViewAnalyzer\Reports\JsonReporter;
use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Tests\TestCase;

class JsonReporterTest extends TestCase
{
    public function test_it_supports_json_format()
    {
        $reporter = new JsonReporter();

        $this->assertTrue($reporter->supports('json'));
        $this->assertFalse($reporter->supports('csv'));
    }

    public function test_it_generates_valid_json()
    {
        $result = new AnalysisResult(
            totalViews: 5,
            usedViews: collect(),
            unusedViews: collect(),
            dynamicViews: collect(),
            statistics: ['test' => 'value']
        );

        $reporter = new JsonReporter();
        $json = $reporter->generate($result);

        $this->assertJson($json);
        $data = json_decode($json, true);

        $this->assertEquals(5, $data['total_views']);
        $this->assertEquals(['test' => 'value'], $data['statistics']);
    }
}
