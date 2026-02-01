<?php

namespace LaravelViewAnalyzer\Tests\Unit\Reports;

use LaravelViewAnalyzer\Reports\ControllerViewReporter;
use LaravelViewAnalyzer\Tests\TestCase;

class ControllerViewReporterTest extends TestCase
{
    public function test_it_generates_table()
    {
        $data = collect([
            [
                'controller' => 'HomeController',
                'namespace' => '(root)',
                'file_path' => 'path/to/HomeController.php',
                'actions' => [
                    [
                        'action' => 'index',
                        'views' => ['pages.home'],
                    ],
                ],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $output = $reporter->generateTable($data);

        $this->assertStringContainsString('HomeController', $output);
        $this->assertStringContainsString('index', $output);
        $this->assertStringContainsString('pages.home', $output);
    }

    public function test_it_generates_tree()
    {
        $data = collect([
            [
                'controller' => 'HomeController',
                'namespace' => '(root)',
                'file_path' => 'path/to/HomeController.php',
                'actions' => [
                    [
                        'action' => 'index',
                        'views' => ['pages.home'],
                    ],
                ],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $output = $reporter->generateTree($data);

        $this->assertStringContainsString('HomeController', $output);
        $this->assertStringContainsString('index', $output);
        $this->assertStringContainsString('pages.home', $output);
    }

    public function test_it_generates_json()
    {
        $data = collect([
            [
                'controller' => 'HomeController',
                'actions' => [],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $json = $reporter->generateJson($data);

        $this->assertJson($json);
        $this->assertStringContainsString('HomeController', $json);
    }

    public function test_it_generates_table_with_group_by_namespace()
    {
        $data = collect([
            [
                'controller' => 'HomeController',
                'namespace' => 'App\Http\Controllers',
                'file_path' => 'path/to/HomeController.php',
                'actions' => [
                    [
                        'action' => 'index',
                        'views' => ['pages.home'],
                    ],
                ],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $output = $reporter->generateTable($data, true);

        $this->assertStringContainsString('Namespace: App\Http\Controllers', $output);
        $this->assertStringContainsString('HomeController', $output);
    }

    public function test_it_generates_table_with_no_actions()
    {
        $data = collect([
            [
                'controller' => 'EmptyController',
                'namespace' => '(root)',
                'actions' => [],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $output = $reporter->generateTable($data);

        $this->assertStringContainsString('EmptyController', $output);
        $this->assertStringContainsString('(no actions)', $output);
    }

    public function test_it_generates_table_with_no_views()
    {
        $data = collect([
            [
                'controller' => 'NoViewController',
                'namespace' => '(root)',
                'actions' => [
                    [
                        'action' => 'store',
                        'views' => [],
                    ],
                ],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $output = $reporter->generateTable($data);

        $this->assertStringContainsString('NoViewController', $output);
        $this->assertStringContainsString('store', $output);
        $this->assertStringContainsString('(no views)', $output);
    }

    public function test_it_generates_tree_with_no_actions()
    {
        $data = collect([
            [
                'controller' => 'EmptyController',
                'namespace' => '(root)',
                'actions' => [],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $output = $reporter->generateTree($data);

        $this->assertStringContainsString('EmptyController', $output);
        $this->assertStringContainsString('(no actions)', $output);
    }

    public function test_it_generates_json_with_group_by_namespace()
    {
        $data = collect([
            [
                'controller' => 'HomeController',
                'namespace' => 'App\Http\Controllers',
                'actions' => [],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $json = $reporter->generateJson($data, true);

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('App\Http\Controllers', $decoded);
    }

    public function test_it_generates_csv_with_escaping_and_no_views()
    {
        $data = collect([
            [
                'controller' => 'Complex, Controller',
                'namespace' => 'App\Namesp"ace',
                'file_path' => "path/to\nfile.php",
                'actions' => [
                    [
                        'action' => 'index',
                        'views' => [],
                    ],
                ],
            ],
        ]);

        $reporter = new ControllerViewReporter();
        $csv = $reporter->generateCsv($data);

        $this->assertStringContainsString('"Complex, Controller"', $csv);
        $this->assertStringContainsString('"App\Namesp""ace"', $csv);
        $this->assertStringContainsString('"path/to', $csv);
        $this->assertStringContainsString('(no views)', $csv);
    }
}
