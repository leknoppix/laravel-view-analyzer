<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\CommandAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class CommandAnalyzerTest extends TestCase
{
    private CommandAnalyzer $analyzer;

    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'scan_paths' => [__DIR__ . '/../../Fixtures/Console/Commands'],
            'exclude_paths' => [],
            'analyzers' => [
                'command' => ['enabled' => true, 'priority' => 35],
            ],
        ];

        $this->analyzer = new CommandAnalyzer($this->config);
    }

    public function test_it_detects_views_in_commands()
    {
        // Ensure fixture exists
        $fixturePath = __DIR__ . '/../../Fixtures/Console/Commands/TestCommand.php';
        if (! file_exists($fixturePath)) {
            $this->markTestSkipped('Fixture file not found: ' . $fixturePath);
        }

        $results = $this->analyzer->analyze();

        // Extract view names from results
        $viewNames = $results->pluck('viewName')->toArray();

        // Check for expected views
        $this->assertContains('emails.test', $viewNames);
        $this->assertContains('emails.facade', $viewNames);
        $this->assertContains('emails.response', $viewNames);
        $this->assertContains('emails.return', $viewNames);
        $this->assertContains('emails.nested', $viewNames);

        // Verify count
        $this->assertCount(5, $results);

        // Verify contexts
        $handleContexts = $results->where('viewName', 'emails.test')->first()->context;
        $this->assertStringContainsString('handle', $handleContexts);

        $nestedContext = $results->where('viewName', 'emails.nested')->first()->context;
        $this->assertStringContainsString('otherMethod', $nestedContext);
    }

    public function test_it_respects_enabled_config()
    {
        $config = $this->config;
        $config['analyzers']['command']['enabled'] = false;

        $analyzer = new CommandAnalyzer($config);

        $this->assertFalse($analyzer->isEnabled());
    }
}
