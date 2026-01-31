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
                    ]
                ]
            ]
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
                    ]
                ]
            ]
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
                'actions' => []
            ]
        ]);

        $reporter = new ControllerViewReporter();
        $json = $reporter->generateJson($data);

        $this->assertJson($json);
        $this->assertStringContainsString('HomeController', $json);
    }

    public function test_it_generates_csv()
    {
        $data = collect([
            [
                'controller' => 'HomeController',
                'namespace' => '(root)',
                'file_path' => 'path.php',
                'actions' => [
                    [
                        'action' => 'index',
                        'views' => ['pages.home'],
                    ]
                ]
            ]
        ]);

        $reporter = new ControllerViewReporter();
        $csv = $reporter->generateCsv($data);

        $this->assertStringContainsString('HomeController', $csv);
        $this->assertStringContainsString('index', $csv);
        $this->assertStringContainsString('pages.home', $csv);
    }
}
