<?php

namespace LaravelViewAnalyzer\Tests\Unit\Detectors;

use LaravelViewAnalyzer\Detectors\DynamicViewDetector;
use LaravelViewAnalyzer\Tests\TestCase;

class DynamicViewDetectorTest extends TestCase
{
    public function test_it_detects_variable_assignments()
    {
        $detector = new DynamicViewDetector();
        $content = '$view = "pages.home"; $template = \'emails.welcome\';';

        $results = $detector->detect($content);

        $this->assertCount(2, $results);
        $this->assertEquals('pages.home', $results[0]['value']);
        $this->assertEquals('emails.welcome', $results[1]['value']);
    }

    public function test_it_has_confidence_score()
    {
        $detector = new DynamicViewDetector();
        $this->assertEquals(0.7, $detector->getConfidenceScore([]));
    }
}
