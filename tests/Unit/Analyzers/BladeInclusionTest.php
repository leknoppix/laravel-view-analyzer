<?php

namespace LaravelViewAnalyzer\Tests\Unit\Analyzers;

use LaravelViewAnalyzer\Analyzers\BladeAnalyzer;
use LaravelViewAnalyzer\Tests\TestCase;

class BladeInclusionTest extends TestCase
{
    protected string $viewPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewPath = base_path('resources/views');
        if (! is_dir($this->viewPath)) {
            mkdir($this->viewPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->viewPath)) {
            $this->recursiveDelete($this->viewPath);
        }
        parent::tearDown();
    }

    private function recursiveDelete($dir)
    {
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (is_dir("$dir/$file")) {
                $this->recursiveDelete("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }
        rmdir($dir);
    }

    public function test_it_detects_nested_inclusions(): void
    {
        // Parent view
        $parentContent = <<<'BLADE'
@extends('layouts.main')
@section('content')
    @include('partials.header')
    @includeIf('partials.optional')
    @includeWhen(true, 'partials.conditional')
    @includeUnless(false, 'partials.unless')
    @includeFirst(['first.view', 'second.view'], ['data' => 'value'])
    
    @component('components.modal', ['id' => 'login'])
        @include('auth.login-form')
    @endcomponent

    @each('users.item', $users, 'user', 'users.empty')
@endsection
BLADE;

        file_put_contents($this->viewPath . '/parent.blade.php', $parentContent);

        // Nested views
        if (! is_dir($this->viewPath . '/partials')) {
            mkdir($this->viewPath . '/partials');
        }
        file_put_contents($this->viewPath . '/partials/header.blade.php', "@include('partials.nav')");
        file_put_contents($this->viewPath . '/partials/nav.blade.php', 'Nav content');

        $analyzer = new BladeAnalyzer([
            'view_paths' => [$this->viewPath],
        ]);

        $results = $analyzer->analyze();

        $viewNames = $results->pluck('viewName')->toArray();

        // Direct inclusions from parent.blade.php
        $this->assertContains('layouts.main', $viewNames);
        $this->assertContains('partials.header', $viewNames);
        $this->assertContains('partials.optional', $viewNames);
        $this->assertContains('partials.conditional', $viewNames);
        $this->assertContains('partials.unless', $viewNames);
        $this->assertContains('first.view', $viewNames);
        $this->assertContains('second.view', $viewNames);
        $this->assertContains('components.modal', $viewNames);
        $this->assertContains('auth.login-form', $viewNames);
        $this->assertContains('users.item', $viewNames);
        $this->assertContains('users.empty', $viewNames);

        // Indirect inclusion
        $this->assertContains('partials.nav', $viewNames);
    }

    public function test_it_detects_tag_based_components(): void
    {
        $content = <<<'BLADE'
<x-alert type="error" :message="$message" />
<x-forms.input name="email" />
<x-modal title="Login">
    <x-auth.login-form />
</x-modal>
BLADE;

        file_put_contents($this->viewPath . '/tags.blade.php', $content);

        $analyzer = new BladeAnalyzer([
            'view_paths' => [$this->viewPath],
        ]);

        $results = $analyzer->analyze();
        $viewNames = $results->pluck('viewName')->toArray();

        // Laravel maps <x-name /> to components.name
        $this->assertContains('components.alert', $viewNames);
        $this->assertContains('components.forms.input', $viewNames);
        $this->assertContains('components.modal', $viewNames);
        $this->assertContains('components.auth.login-form', $viewNames);
    }
}
