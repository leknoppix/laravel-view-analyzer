<?php

namespace LaravelViewAnalyzer\Tests\Unit\Support;

use LaravelViewAnalyzer\Support\PatternMatcher;
use PHPUnit\Framework\TestCase;

class PatternMatcherTest extends TestCase
{
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
