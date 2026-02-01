<?php

namespace LaravelViewAnalyzer\Tests\Unit\Support;

use LaravelViewAnalyzer\Support\PatternMatcher;
use PHPUnit\Framework\TestCase;

class PatternMatcherTest extends TestCase
{
    public function test_it_matches_standard_view_calls(): void
    {
        $content = 'view("test.view"); view(\'other.view\'); $response->view("resp.view"); View::make("facade.view");';
        $matches = PatternMatcher::matchViewCall($content);

        $viewNames = array_column($matches, 'view');
        $this->assertContains('test.view', $viewNames);
        $this->assertContains('other.view', $viewNames);
        $this->assertContains('resp.view', $viewNames);
        $this->assertContains('facade.view', $viewNames);
    }

    public function test_it_matches_blade_directives(): void
    {
        $content = '@include("inc.view") @extends(\'ext.view\') @includeIf("if.view")';

        $includeMatches = PatternMatcher::matchBladeDirective($content, 'include');
        $this->assertEquals('inc.view', $includeMatches[0]['view']);

        $extendsMatches = PatternMatcher::matchBladeDirective($content, 'extends');
        $this->assertEquals('ext.view', $extendsMatches[0]['view']);
    }

    public function test_it_matches_include_when_unless(): void
    {
        $content = '@includeWhen($cond, "when.view") @includeUnless($cond, "unless.view")';

        $whenMatches = PatternMatcher::matchBladeDirective($content, 'includeWhen');
        $this->assertEquals('when.view', $whenMatches[0]['view']);

        $unlessMatches = PatternMatcher::matchBladeDirective($content, 'includeUnless');
        $this->assertEquals('unless.view', $unlessMatches[0]['view']);
    }

    public function test_it_matches_include_first(): void
    {
        $content = '@includeFirst(["first.view", "second.view"])';
        $matches = PatternMatcher::matchBladeDirective($content, 'includeFirst');

        $viewNames = array_column($matches, 'view');
        $this->assertContains('first.view', $viewNames);
        $this->assertContains('second.view', $viewNames);
    }

    public function test_it_matches_each_directive(): void
    {
        $content = '@each("item.view", $items, "item", "empty.view")';
        $matches = PatternMatcher::matchBladeDirective($content, 'each');

        $viewNames = array_column($matches, 'view');
        $this->assertContains('item.view', $viewNames);
        $this->assertContains('empty.view', $viewNames);
    }

    public function test_it_matches_component_tags(): void
    {
        $content = '<x-alert /> <x-forms.input />';
        $matches = PatternMatcher::matchComponentTag($content);

        $viewNames = array_column($matches, 'view');
        $this->assertContains('components.alert', $viewNames);
        $this->assertContains('components.forms.input', $viewNames);
    }

    public function test_it_handles_exclusion_logic(): void
    {
        $this->assertTrue(PatternMatcher::shouldExclude('/path/to/vendor/package', ['vendor']));
        $this->assertTrue(PatternMatcher::shouldExclude('/path/to/node_modules/pkg', ['node_modules']));
        $this->assertFalse(PatternMatcher::shouldExclude('/path/to/app/Http', ['vendor']));
    }

    public function test_it_matches_exact_view_names(): void
    {
        $patterns = ['auth.login', 'home'];

        $this->assertTrue(PatternMatcher::matchesViewPattern('auth.login', $patterns));
        $this->assertTrue(PatternMatcher::matchesViewPattern('home', $patterns));
        $this->assertFalse(PatternMatcher::matchesViewPattern('auth.register', $patterns));
    }

    public function test_it_matches_with_wildcards(): void
    {
        $patterns = ['auth.*', 'layouts.*', 'admin.pages.*'];

        $this->assertTrue(PatternMatcher::matchesViewPattern('auth.login', $patterns));
        $this->assertTrue(PatternMatcher::matchesViewPattern('auth.register', $patterns));
        $this->assertTrue(PatternMatcher::matchesViewPattern('auth.passwords.email', $patterns));
        $this->assertTrue(PatternMatcher::matchesViewPattern('layouts.app', $patterns));
        $this->assertTrue(PatternMatcher::matchesViewPattern('admin.pages.edit', $patterns));

        $this->assertFalse(PatternMatcher::matchesViewPattern('home', $patterns));
        $this->assertFalse(PatternMatcher::matchesViewPattern('profile.show', $patterns));
    }

    public function test_it_handles_complex_wildcards(): void
    {
        $patterns = ['*.index', 'users.*.profile'];

        $this->assertTrue(PatternMatcher::matchesViewPattern('posts.index', $patterns));
        $this->assertTrue(PatternMatcher::matchesViewPattern('users.index', $patterns));
        $this->assertTrue(PatternMatcher::matchesViewPattern('users.admin.profile', $patterns));

        $this->assertFalse(PatternMatcher::matchesViewPattern('posts.show', $patterns));
    }

    public function test_it_matches_pagination_view_calls(): void
    {
        $content = '$users->links("pagination.custom")';
        $matches = PatternMatcher::matchViewCall($content);

        $this->assertCount(1, $matches, 'Should match ->links("pagination.custom")');
        $this->assertEquals('pagination.custom', $matches[0]['view']);

        $content = '$users->links(\'pagination.custom\')';
        $matches = PatternMatcher::matchViewCall($content);

        $this->assertCount(1, $matches, 'Should match ->links(\'pagination.custom\')');
        $this->assertEquals('pagination.custom', $matches[0]['view']);
    }

    public function test_it_matches_paginator_default_views(): void
    {
        $content = 'Paginator::defaultView("pagination.custom")';
        $matches = PatternMatcher::matchViewCall($content);

        $this->assertCount(1, $matches, 'Should match Paginator::defaultView("pagination.custom")');
        $this->assertEquals('pagination.custom', $matches[0]['view']);

        $content = 'Paginator::defaultSimpleView(\'pagination.simple\')';
        $matches = PatternMatcher::matchViewCall($content);

        $this->assertCount(1, $matches, 'Should match Paginator::defaultSimpleView(\'pagination.simple\')');
        $this->assertEquals('pagination.simple', $matches[0]['view']);
    }
}
