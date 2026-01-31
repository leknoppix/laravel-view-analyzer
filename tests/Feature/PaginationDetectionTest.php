<?php

namespace LaravelViewAnalyzer\Tests\Feature;

use LaravelViewAnalyzer\Analyzers\BladeAnalyzer;
use LaravelViewAnalyzer\Analyzers\ProviderAnalyzer;
use PHPUnit\Framework\TestCase;

class PaginationDetectionTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/view-analyzer-test-' . uniqid();
        mkdir($this->tempDir, 0777, true);
        mkdir($this->tempDir . '/views', 0777, true);
        mkdir($this->tempDir . '/Providers', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    protected function removeDirectory($dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    public function test_it_detects_links_in_blade_files(): void
    {
        $bladeContent = '{{ $users->links("pagination.custom") }}';
        file_put_contents($this->tempDir . '/views/test.blade.php', $bladeContent);

        $analyzer = new BladeAnalyzer([
            'view_paths' => [$this->tempDir . '/views'],
            'analyzers' => ['blade' => ['enabled' => true, 'priority' => 20]],
        ]);

        $results = $analyzer->analyze();

        $this->assertTrue(
            $results->contains(fn ($ref) => $ref->viewName === 'pagination.custom'),
            'Should detect pagination.custom in blade file'
        );
    }

    public function test_it_detects_paginator_default_view_in_service_providers(): void
    {
        $phpContent = '<?php namespace App\Providers; use Illuminate\Pagination\Paginator; class AppServiceProvider { public function boot() { Paginator::defaultView("pagination.global"); } }';
        file_put_contents($this->tempDir . '/Providers/AppServiceProvider.php', $phpContent);

        $analyzer = new ProviderAnalyzer([
            'scan_paths' => [$this->tempDir . '/Providers'],
            'analyzers' => ['provider' => ['enabled' => true, 'priority' => 70]],
        ]);

        $results = $analyzer->analyze();

        $this->assertTrue(
            $results->contains(fn ($ref) => $ref->viewName === 'pagination.global'),
            'Should detect pagination.global in ServiceProvider'
        );

        $ref = $results->first(fn ($ref) => $ref->viewName === 'pagination.global');
        $this->assertEquals('provider', $ref->type);
    }
}
