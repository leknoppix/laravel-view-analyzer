<?php

namespace LaravelViewAnalyzer\Tests\Unit\Support;

use LaravelViewAnalyzer\Support\PathHelper;
use LaravelViewAnalyzer\Tests\TestCase;

class PathHelperTest extends TestCase
{
    public function test_it_normalizes_paths()
    {
        $this->assertEquals('foo/bar/baz', PathHelper::normalize('foo\bar\baz'));
        $this->assertEquals('/foo/bar', PathHelper::normalize('\foo\bar'));
        $this->assertEquals('C:/Windows/System32', PathHelper::normalize('C:\Windows\System32'));
    }

    public function test_it_makes_paths_relative()
    {
        $base = '/var/www/html';
        $path = '/var/www/html/app/Http/Controllers';

        $this->assertEquals('app/Http/Controllers', PathHelper::makeRelative($path, $base));
        $this->assertEquals('app/Http/Controllers', PathHelper::makeRelative($path, $base . '/'));

        // Path not in base
        $otherPath = '/etc/nginx';
        $this->assertEquals('/etc/nginx', PathHelper::makeRelative($otherPath, $base));
    }

    public function test_it_makes_paths_absolute()
    {
        $base = '/var/www/html';

        // Already absolute
        $this->assertEquals('/var/www/html/app', PathHelper::makeAbsolute('/var/www/html/app', $base));

        // Relative
        $this->assertEquals('/var/www/html/app', PathHelper::makeAbsolute('app', $base));
        $this->assertEquals('/var/www/html/sub/dir', PathHelper::makeAbsolute('sub/dir', $base));
    }

    public function test_it_checks_if_path_is_absolute()
    {
        // Unix
        $this->assertTrue(PathHelper::isAbsolute('/var/www'));
        $this->assertFalse(PathHelper::isAbsolute('var/www'));

        // Windows (handled via normalization)
        $this->assertTrue(PathHelper::isAbsolute('C:/Windows'));
        $this->assertTrue(PathHelper::isAbsolute('C:\Windows')); // Normalized to C:/Windows

        $this->assertFalse(PathHelper::isAbsolute('Windows\System32'));
    }
}
