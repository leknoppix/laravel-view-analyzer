<?php

namespace LaravelViewAnalyzer\Tests\Unit\Results;

use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Tests\TestCase;

class ViewReferenceTest extends TestCase
{
    public function test_it_creates_view_reference_and_converts_to_array()
    {
        $reference = new ViewReference(
            viewName: 'pages.home',
            sourceFile: '/app/Http/Controllers/HomeController.php',
            lineNumber: 42,
            context: 'Controller::index',
            type: 'controller',
            isDynamic: false
        );

        $this->assertEquals('pages.home', $reference->viewName);
        $this->assertEquals('/app/Http/Controllers/HomeController.php', $reference->sourceFile);
        $this->assertEquals(42, $reference->lineNumber);
        $this->assertEquals('Controller::index', $reference->context);
        $this->assertEquals('controller', $reference->type);
        $this->assertFalse($reference->isDynamic);

        $array = $reference->toArray();

        $this->assertEquals([
            'view_name' => 'pages.home',
            'source_file' => '/app/Http/Controllers/HomeController.php',
            'line_number' => 42,
            'context' => 'Controller::index',
            'type' => 'controller',
            'is_dynamic' => false,
        ], $array);
    }
}
