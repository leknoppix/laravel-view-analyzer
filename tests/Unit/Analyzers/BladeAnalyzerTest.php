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

    public function test_it_has_default_priority(): void
    {
        $analyzer = new BladeAnalyzer();
        $this->assertEquals(20, $analyzer->getPriority());
    }

    public function test_it_respects_enabled_config(): void
    {
        $analyzer = new BladeAnalyzer(['analyzers' => ['blade' => ['enabled' => false]]]);
        $this->assertFalse($analyzer->isEnabled());

        $analyzer = new BladeAnalyzer(['analyzers' => ['blade' => ['enabled' => true]]]);
        $this->assertTrue($analyzer->isEnabled());
    }

    public function test_it_detects_tag_based_components(): void
    {
        $content = <<<'BLADE'
<x-alert type="error" />
<x-forms.input name="test" />
BLADE;
        $tempViewPath = base_path('resources/views/test_tags.blade.php');
        file_put_contents($tempViewPath, $content);

        $analyzer = new BladeAnalyzer(['view_paths' => [base_path('resources/views')]]);
        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();
        $this->assertContains('components.alert', $viewNames);
        $this->assertContains('components.forms.input', $viewNames);

        unlink($tempViewPath);
    }

    public function test_it_detects_blade_view_calls(): void
    {
        $content = <<<'BLADE'
{{ $users->links('pagination.custom') }}
<div>{{ view('partials.popup') }}</div>
BLADE;
        $tempViewPath = base_path('resources/views/test_calls.blade.php');
        file_put_contents($tempViewPath, $content);

        $analyzer = new BladeAnalyzer(['view_paths' => [base_path('resources/views')]]);
        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();
        $this->assertContains('pagination.custom', $viewNames);
        $this->assertContains('partials.popup', $viewNames);

        unlink($tempViewPath);
    }

    public function test_it_skips_invalid_directories(): void
    {
        $analyzer = new BladeAnalyzer(['view_paths' => ['/non/existent/views']]);
        $results = $analyzer->analyze();
        $this->assertCount(0, $results);
    }

    public function test_it_uses_default_view_paths_when_config_is_empty(): void
    {
        $analyzer = new BladeAnalyzer([]);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $analyzer->analyze());
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

    public function test_it_skips_empty_files(): void
    {
        $tempViewPath = base_path('resources/views/empty.blade.php');
        touch($tempViewPath);

        $analyzer = new BladeAnalyzer([
            'view_paths' => [base_path('resources/views')],
        ]);

        $results = $analyzer->analyze();

        $this->assertCount(0, $results);

        unlink($tempViewPath);
    }
}
