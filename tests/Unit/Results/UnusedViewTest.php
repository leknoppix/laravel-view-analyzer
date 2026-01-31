<?php

namespace LaravelViewAnalyzer\Tests\Unit\Results;

use Carbon\Carbon;
use LaravelViewAnalyzer\Results\UnusedView;
use LaravelViewAnalyzer\Tests\TestCase;

class UnusedViewTest extends TestCase
{
    public function test_it_creates_unused_view_and_formats_size()
    {
        $date = Carbon::parse('2023-01-01 12:00:00');
        $unused = new UnusedView(
            viewName: 'unused.view',
            filePath: '/path/to/view.blade.php',
            fileSize: 2048, // 2 KB
            lastModified: $date
        );

        $array = $unused->toArray();

        $this->assertEquals('unused.view', $array['view_name']);
        $this->assertEquals('/path/to/view.blade.php', $array['file_path']);
        $this->assertEquals(2048, $array['file_size']);
        $this->assertEquals('2 KB', $array['file_size_human']);
        $this->assertEquals('2023-01-01 12:00:00', $array['last_modified']);
    }

    public function test_it_formats_various_file_sizes()
    {
        $date = now();

        $unusedBytes = new UnusedView('view', 'path', 500, $date);
        $this->assertEquals('500 B', $unusedBytes->toArray()['file_size_human']);

        $unusedMb = new UnusedView('view', 'path', 1024 * 1024 * 2.5, $date);
        $this->assertEquals('2.5 MB', $unusedMb->toArray()['file_size_human']);
    }
}
