<?php

namespace LaravelViewAnalyzer\Tests\Unit\Scanners;

use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Tests\TestCase;

class DirectoryScannerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/directory_scanner_test_' . uniqid();
        mkdir($this->tempDir);
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

    public function test_it_scans_directory_for_files()
    {
        touch($this->tempDir . '/file1.php');
        touch($this->tempDir . '/file2.txt');
        mkdir($this->tempDir . '/subdir');
        touch($this->tempDir . '/subdir/file3.php');

        $scanner = new DirectoryScanner($this->tempDir, '*.php');
        $files = $scanner->scan();

        $this->assertCount(2, $files);
        $this->assertContains(realpath($this->tempDir . '/file1.php'), $files);
        $this->assertContains(realpath($this->tempDir . '/subdir/file3.php'), $files);
        $this->assertNotContains(realpath($this->tempDir . '/file2.txt'), $files);
    }

    public function test_it_respects_exclude_paths()
    {
        touch($this->tempDir . '/file1.php');
        mkdir($this->tempDir . '/vendor');
        touch($this->tempDir . '/vendor/file2.php');

        $scanner = new DirectoryScanner($this->tempDir, '*.php', ['vendor']);
        $files = $scanner->scan();

        $this->assertCount(1, $files);
        $this->assertContains(realpath($this->tempDir . '/file1.php'), $files);
    }

    public function test_it_returns_empty_array_if_directory_does_not_exist()
    {
        $scanner = new DirectoryScanner('/non/existent/directory');
        $this->assertEquals([], $scanner->scan());
    }
}
