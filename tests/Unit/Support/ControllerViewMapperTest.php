<?php

namespace LaravelViewAnalyzer\Tests\Unit\Support;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Support\ControllerViewMapper;
use LaravelViewAnalyzer\Tests\TestCase;

class ControllerViewMapperTest extends TestCase
{
    public function test_it_maps_controllers_to_views()
    {
        // Setup mock data
        $controllerPath = app_path('Http/Controllers');

        // 1. HomeController::index -> pages.home
        $ref1 = new ViewReference(
            viewName: 'pages.home',
            sourceFile: $controllerPath . '/HomeController.php',
            lineNumber: 15,
            context: 'HomeController::index',
            type: 'controller'
        );

        // 2. Admin\DashboardController::index -> admin.dashboard
        $ref2 = new ViewReference(
            viewName: 'admin.dashboard',
            sourceFile: $controllerPath . '/Admin/DashboardController.php',
            lineNumber: 20,
            context: 'DashboardController::index',
            type: 'controller'
        );

        // 3. Admin\DashboardController::index -> admin.stats (same action, second view)
        $ref3 = new ViewReference(
            viewName: 'admin.stats',
            sourceFile: $controllerPath . '/Admin/DashboardController.php',
            lineNumber: 25,
            context: 'DashboardController::index',
            type: 'controller'
        );

        $usedViews = collect([
            new ViewUsage('pages.home', collect([$ref1])),
            new ViewUsage('admin.dashboard', collect([$ref2])),
            new ViewUsage('admin.stats', collect([$ref3])),
        ]);

        $result = new AnalysisResult(
            totalViews: 3,
            usedViews: $usedViews,
            unusedViews: collect(),
            dynamicViews: collect()
        );

        // Execute Mapper
        $mapper = new ControllerViewMapper($result);
        $map = $mapper->mapControllersToViews();

        // Assertions
        $this->assertCount(2, $map); // HomeController + Admin\DashboardController

        // Check HomeController
        $homeCtrl = $map->firstWhere('controller', 'HomeController');
        $this->assertNotNull($homeCtrl);
        $this->assertEquals('(root)', $homeCtrl['namespace']);
        $this->assertCount(1, $homeCtrl['actions']);
        $this->assertEquals('index', $homeCtrl['actions'][0]['action']);
        $this->assertEquals(['pages.home'], $homeCtrl['actions'][0]['views']);

        // Check Admin\DashboardController
        $adminCtrl = $map->firstWhere('controller', 'Admin\DashboardController');
        $this->assertNotNull($adminCtrl);
        $this->assertEquals('Admin', $adminCtrl['namespace']);
        $this->assertCount(1, $adminCtrl['actions']);
        $this->assertEquals('index', $adminCtrl['actions'][0]['action']);

        // Check multiple views in same action
        $views = $adminCtrl['actions'][0]['views'];
        sort($views);
        $this->assertEquals(['admin.dashboard', 'admin.stats'], $views);
    }
}
