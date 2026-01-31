<?php

namespace LaravelViewAnalyzer\Tests\Unit\Results;

use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\UnusedView;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Tests\TestCase;

class AnalysisResultTest extends TestCase
{
    public function test_it_creates_result_and_aggregates_views()
    {
        $used = collect([new ViewUsage('used.view', collect([]))]);
        $unused = collect([new UnusedView('unused.view', 'path', 0, now())]);
        $dynamic = collect([]);

        $result = new AnalysisResult(
            totalViews: 2,
            usedViews: $used,
            unusedViews: $unused,
            dynamicViews: $dynamic,
            statistics: ['stat' => 1]
        );

        $this->assertEquals(2, $result->totalViews);
        $this->assertCount(1, $result->getUsedViews());
        $this->assertCount(1, $result->getUnusedViews());

        $allViews = $result->getAllViews();
        $this->assertCount(2, $allViews);
        $this->assertEquals('used.view', $allViews->first()->viewName);
    }

    public function test_it_converts_to_array()
    {
        $used = collect([new ViewUsage('used.view', collect([]))]);
        $unused = collect([new UnusedView('unused.view', 'path', 0, now())]);
        $dynamic = collect([]);

        $result = new AnalysisResult(
            totalViews: 10,
            usedViews: $used,
            unusedViews: $unused,
            dynamicViews: $dynamic,
            statistics: ['foo' => 'bar'],
            warnings: ['warning 1']
        );

        $array = $result->toArray();

        $this->assertEquals(10, $array['total_views']);
        $this->assertEquals(1, $array['used_views_count']);
        $this->assertEquals(1, $array['unused_views_count']);
        $this->assertEquals(0, $array['dynamic_views_count']);
        $this->assertEquals(['foo' => 'bar'], $array['statistics']);
        $this->assertEquals(['warning 1'], $array['warnings']);
        $this->assertIsArray($array['used_views']);
        $this->assertIsArray($array['unused_views']);
    }
}
