<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\BladeAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class BladeAnalyzerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! is_dir(base_path('resources/views'))) {
            mkdir(base_path('resources/views'), 0777, true);
        }
    }

    public function test_it_has_correct_name(): void
    {
        $analyzer = new BladeAnalyzer();
        $this->assertEquals('Blade Analyzer', $analyzer->getName());
    }

    public function test_it_detects_standard_directives(): void
    {
        $content = <<<'BLADE'
@extends('layouts.app')
@include('partials.header')
@includeIf('partials.banner')
@each('users.card', $users, 'user')
BLADE;

        $tempViewPath = base_path('resources/views/test_directives.blade.php');
        file_put_contents($tempViewPath, $content);

        $analyzer = new BladeAnalyzer([
            'view_paths' => [base_path('resources/views')],
        ]);

        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();

        $this->assertContains('layouts.app', $viewNames);
        $this->assertContains('partials.header', $viewNames);
        $this->assertContains('partials.banner', $viewNames);
        $this->assertContains('users.card', $viewNames);

        unlink($tempViewPath);
    }

    public function test_it_detects_component_syntax(): void
    {
        $content = <<<'BLADE'
@component('components.alert')
    <strong>Whoops!</strong>
@endcomponent
BLADE;

        $tempViewPath = base_path('resources/views/test_component.blade.php');
        file_put_contents($tempViewPath, $content);

        $analyzer = new BladeAnalyzer([
            'view_paths' => [base_path('resources/views')],
        ]);

        $results = $analyzer->analyze();

        $this->assertTrue($results->contains('viewName', 'components.alert'));

        unlink($tempViewPath);
    }
}
