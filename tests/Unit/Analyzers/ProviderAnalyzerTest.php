<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\ProviderAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class ProviderAnalyzerTest extends TestCase
{
    public function test_it_has_correct_name()
    {
        $analyzer = new ProviderAnalyzer();
        $this->assertEquals('Provider Analyzer', $analyzer->getName());
    }

    public function test_it_has_correct_priority()
    {
        $analyzer = new ProviderAnalyzer();
        $this->assertEquals(70, $analyzer->getPriority());

        $analyzer = new ProviderAnalyzer(['analyzers' => ['provider' => ['priority' => 100]]]);
        $this->assertEquals(100, $analyzer->getPriority());
    }

    public function test_it_respects_enabled_config()
    {
        $analyzer = new ProviderAnalyzer(['analyzers' => ['provider' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());

        $analyzer = new ProviderAnalyzer(['analyzers' => ['provider' => ['enabled' => true]]]);
        $this->assertTrue($analyzer->isEnabled());
    }

    public function test_it_skips_invalid_directories()
    {
        $analyzer = new ProviderAnalyzer(['scan_paths' => ['/non/existent/path']]);
        $results = $analyzer->analyze();
        $this->assertCount(0, $results);
    }

    public function test_it_skips_non_provider_files()
    {
        $tempDir = sys_get_temp_dir() . '/view-test-' . uniqid();
        mkdir($tempDir);

        // This file should be skipped because it doesn't end in ServiceProvider.php
        // and isn't in a Providers directory
        $file = $tempDir . '/NotAProvider.php';
        file_put_contents($file, '<?php view("test");');

        $analyzer = new ProviderAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($file);
        rmdir($tempDir);
    }

    public function test_it_uses_default_app_path_when_config_is_empty()
    {
        // On vérifie que l'analyseur ne plante pas et retourne une collection
        // même si la config est vide (il tentera d'utiliser app_path)
        $analyzer = new ProviderAnalyzer(['providers_path' => '/non/existent/providers']);
        $results = $analyzer->analyze();
        $this->assertCount(0, $results);
    }

    public function test_it_skips_unreadable_files()
    {
        $tempDir = sys_get_temp_dir() . '/view-test-unreadable-' . uniqid();
        mkdir($tempDir);
        $file = $tempDir . '/UnreadableServiceProvider.php';
        touch($file);
        chmod($file, 0000);

        $analyzer = new ProviderAnalyzer(['scan_paths' => [$tempDir]]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        chmod($file, 0644);
        unlink($file);
        rmdir($tempDir);
    }

    public function test_it_returns_empty_if_providers_directory_exists_but_is_empty(): void
    {
        $tempDir = sys_get_temp_dir() . '/view_test_Providers_empty_dir_' . uniqid();
        mkdir($tempDir);

        $analyzer = new ProviderAnalyzer(['providers_path' => $tempDir]);
        $results = $analyzer->analyze();

        $this->assertCount(0, $results);
        rmdir($tempDir);
    }

    public function test_it_uses_default_app_path_when_no_config_provided()
    {
        // On s'assure qu'un dossier de providers existe
        $path = app_path('Providers');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $analyzer = new ProviderAnalyzer();
        $results = $analyzer->analyze();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
    }
}
