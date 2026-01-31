<?php

namespace LaravelViewAnalyzer\Tests\Unit\Scanners;

use LaravelViewAnalyzer\Scanners\FileScanner;
use LaravelViewAnalyzer\Tests\TestCase;

class FileScannerTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFile = sys_get_temp_dir() . '/file_scanner_test_' . uniqid() . '.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
        parent::tearDown();
    }

    public function test_it_returns_file_path_on_scan()
    {
        file_put_contents($this->tempFile, 'content');
        $scanner = new FileScanner($this->tempFile);

        $this->assertEquals([$this->tempFile], $scanner->scan());
    }

    public function test_it_returns_empty_array_if_file_does_not_exist()
    {
        $scanner = new FileScanner('/non/existent/file.php');
        $this->assertEquals([], $scanner->scan());
    }

    public function test_it_reads_content()
    {
        $content = '<?php echo "hello";';
        file_put_contents($this->tempFile, $content);

        $scanner = new FileScanner($this->tempFile);
        $this->assertEquals($content, $scanner->readContent());
    }

    public function test_it_returns_null_content_if_file_does_not_exist()
    {
        $scanner = new FileScanner('/non/existent/file.php');
        $this->assertNull($scanner->readContent());
    }

    public function test_it_calculates_line_numbers()
    {
        $content = "Line 1\nLine 2\nLine 3\nLine 4";
        file_put_contents($this->tempFile, $content);
        $scanner = new FileScanner($this->tempFile);

        // Position of start of Line 1 is 0 -> Line 1
        $this->assertEquals(1, $scanner->getLineNumber(0));

        // Position of start of Line 3
        $pos = strpos($content, 'Line 3');
        $this->assertEquals(3, $scanner->getLineNumber($pos));
    }

    public function test_it_detects_method_at_position()
    {
        $content = <<<'PHP'
<?php
class Test {
    public function firstMethod() {
        // Position A inside firstMethod
    }

    public function secondMethod($arg) {
        // Position B inside secondMethod
    }
}
PHP;
        file_put_contents($this->tempFile, $content);
        $scanner = new FileScanner($this->tempFile);

        // Find position inside firstMethod
        $posA = strpos($content, '// Position A');
        $this->assertEquals('firstMethod', $scanner->getMethodAtPosition($posA));

        // Find position inside secondMethod
        $posB = strpos($content, '// Position B');
        $this->assertEquals('secondMethod', $scanner->getMethodAtPosition($posB));
    }

    public function test_it_returns_unknown_method_if_not_found()
    {
        $content = '<?php // Just a comment';
        file_put_contents($this->tempFile, $content);
        $scanner = new FileScanner($this->tempFile);

        $this->assertEquals('unknown', $scanner->getMethodAtPosition(10));
    }
}
