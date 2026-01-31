<?php

namespace LaravelViewAnalyzer\Tests;

use LaravelViewAnalyzer\ViewAnalyzerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ViewAnalyzerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configuration de base pour les tests
    }
}
