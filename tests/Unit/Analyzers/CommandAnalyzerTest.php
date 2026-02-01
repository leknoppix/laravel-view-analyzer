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

    public function test_it_has_correct_name()
    {
        $this->assertEquals('Command Analyzer', $this->analyzer->getName());
    }

    public function test_it_has_default_priority()
    {
        $this->assertEquals(35, $this->analyzer->getPriority());
    }

    public function test_it_skips_invalid_directories()
    {
        $analyzer = new CommandAnalyzer(['scan_paths' => ['/non/existent/Console']]);
        $results = $analyzer->analyze();
        $this->assertCount(0, $results);
    }

    public function test_it_skips_empty_files(): void
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Console_' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/EmptyCommand.php';
        touch($file);

        $analyzer = new CommandAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($file);
        rmdir($tempDir);
    }

    public function test_it_filters_scan_paths_for_console_keyword()
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Console_' . uniqid();
        $otherDir = sys_get_temp_dir() . '/view_test_Other_' . uniqid();
        mkdir($tempDir);
        mkdir($otherDir);

        $file1 = $tempDir . '/TestCommand.php';
        $file2 = $otherDir . '/OtherFile.php';
        file_put_contents($file1, '<?php view("test1");');
        file_put_contents($file2, '<?php view("test2");');

        // Should only pick up $tempDir because it contains "Console" (simulated via $this->config context)
        // Actually, the analyzer filters by checking if the path string contains 'Console'
        $analyzer = new CommandAnalyzer(['scan_paths' => [$tempDir, $otherDir]]);
        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();
        $this->assertContains('test1', $viewNames);
        $this->assertNotContains('test2', $viewNames);

        unlink($file1);
        unlink($file2);
        rmdir($tempDir);
        rmdir($otherDir);
    }

    public function test_it_uses_default_app_path_when_no_console_paths_found()
    {
        $analyzer = new CommandAnalyzer(['scan_paths' => []]);
        $results = $analyzer->analyze();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }
}
