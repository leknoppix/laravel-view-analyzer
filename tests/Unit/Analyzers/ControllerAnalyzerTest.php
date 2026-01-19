<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\ControllerAnalyzer;
use PHPUnit\Framework\TestCase;

class ControllerAnalyzerTest extends TestCase
{
    public function test_it_has_correct_name(): void
    {
        $analyzer = new ControllerAnalyzer;

        $this->assertEquals('Controller Analyzer', $analyzer->getName());
    }

    public function test_it_is_enabled_by_default(): void
    {
        $analyzer = new ControllerAnalyzer;

        $this->assertTrue($analyzer->isEnabled());
    }

    public function test_it_has_correct_priority(): void
    {
        $analyzer = new ControllerAnalyzer([
            'analyzers' => [
                'controller' => ['priority' => 10],
            ],
        ]);

        $this->assertEquals(10, $analyzer->getPriority());
    }
}
