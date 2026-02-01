<?php

namespace LaravelViewAnalyzer\Tests\Unit\Results;

use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Tests\TestCase;

class ViewUsageTest extends TestCase
{
    public function test_it_creates_view_usage_and_extracts_metadata()
    {
        $ref1 = new ViewReference('view.name', 'file1.php', 10, 'ctx1', 'controller');
        $ref2 = new ViewReference('view.name', 'file2.php', 20, 'ctx2', 'blade');

        $references = collect([$ref1, $ref2]);

        $usage = new ViewUsage('view.name', $references, '/absolute/path/to/view.blade.php');

        $this->assertEquals('view.name', $usage->viewName);
        $this->assertEquals('/absolute/path/to/view.blade.php', $usage->filePath);
        $this->assertEquals(2, $usage->referenceCount); // Should be auto-calculated
        $this->assertEquals(['controller', 'blade'], $usage->types); // Should extract unique types
    }

    public function test_it_converts_to_array()
    {
        $ref = new ViewReference('view.name', 'file.php', 10, 'ctx', 'controller');
        $usage = new ViewUsage('view.name', collect([$ref]), '/absolute/path/to/view.blade.php');

        $array = $usage->toArray();

        $this->assertEquals('view.name', $array['view_name']);
        $this->assertEquals('/absolute/path/to/view.blade.php', $array['file_path']);
        $this->assertEquals(1, $array['reference_count']);
        $this->assertEquals(['controller'], $array['types']);
        $this->assertCount(1, $array['references']);
        $this->assertEquals('file.php', $array['references'][0]['source_file']);
    }
}
