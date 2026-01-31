<?php

namespace LaravelViewAnalyzer\Tests\Unit\Support;

use LaravelViewAnalyzer\Support\ViewPathResolver;
use LaravelViewAnalyzer\Tests\TestCase;

class ViewPathResolverTest extends TestCase
{
    public function test_it_converts_file_path_to_view_name()
    {
        $viewsPath = '/var/www/resources/views';

        $this->assertEquals(
            'pages.home',
            ViewPathResolver::toViewName('/var/www/resources/views/pages/home.blade.php', $viewsPath)
        );

        $this->assertEquals(
            'layouts.app',
            ViewPathResolver::toViewName('/var/www/resources/views/layouts/app.blade.php', $viewsPath)
        );
    }

    public function test_it_converts_view_name_to_file_path()
    {
        $viewsPath = '/var/www/resources/views';

        $this->assertEquals(
            '/var/www/resources/views/pages/home.blade.php',
            ViewPathResolver::toFilePath('pages.home', $viewsPath)
        );
    }

    public function test_it_resolves_namespaces()
    {
        // Standard view
        $this->assertEquals(
            ['namespace' => null, 'view' => 'pages.home'],
            ViewPathResolver::resolveNamespace('pages.home')
        );

        // Namespaced view
        $this->assertEquals(
            ['namespace' => 'package', 'view' => 'components.alert'],
            ViewPathResolver::resolveNamespace('package::components.alert')
        );
    }
}
