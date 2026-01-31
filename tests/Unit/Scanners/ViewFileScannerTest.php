<?php

namespace LaravelViewAnalyzer\Tests\Unit\Scanners;

use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\ViewFileScanner;
use LaravelViewAnalyzer\Tests\TestCase;

class ViewFileScannerTest extends TestCase
{
    public function test_it_scans_and_resolves_view_names()
    {
        // We mock the DirectoryScanner indirectly or setup a real test environment.
        // Since DirectoryScanner is instantiated inside scan(), it's hard to mock without dependency injection.
        // However, we can test the logic by creating a temporary directory structure or subclassing.
        // Given the code structure, testing with real temporary files is more robust here.

        $tempDir = sys_get_temp_dir() . '/view_scanner_test_' . uniqid();
        mkdir($tempDir);
        mkdir($tempDir . '/auth');
        mkdir($tempDir . '/layouts');

        touch($tempDir . '/welcome.blade.php');
        touch($tempDir . '/auth/login.blade.php');
        touch($tempDir . '/layouts/app.blade.php');

        $scanner = new ViewFileScanner([$tempDir]);
        $views = $scanner->scan();

        $this->assertArrayHasKey('welcome', $views);
        $this->assertArrayHasKey('auth.login', $views);
        $this->assertArrayHasKey('layouts.app', $views);

        // Cleanup
        unlink($tempDir . '/welcome.blade.php');
        unlink($tempDir . '/auth/login.blade.php');
        unlink($tempDir . '/layouts/app.blade.php');
        rmdir($tempDir . '/auth');
        rmdir($tempDir . '/layouts');
        rmdir($tempDir);
    }

    public function test_it_returns_collection_registry()
    {
        $tempDir = sys_get_temp_dir() . '/view_scanner_test_coll_' . uniqid();
        mkdir($tempDir);
        touch($tempDir . '/home.blade.php');

        $scanner = new ViewFileScanner([$tempDir]);
        $registry = $scanner->getViewRegistry();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $registry);
        $this->assertTrue($registry->has('home'));

        unlink($tempDir . '/home.blade.php');
        rmdir($tempDir);
    }
}
