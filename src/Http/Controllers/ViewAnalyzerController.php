<?php

namespace LaravelViewAnalyzer\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelViewAnalyzer\Support\ControllerViewMapper;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewAnalyzerController extends Controller
{
    public function index()
    {
        $analyzer = new ViewAnalyzer(config('view-analyzer'));
        $result = $analyzer->analyze();

        $mapper = new ControllerViewMapper($result);
        $controllerMap = $mapper->mapControllersToViews();

        $stats = [
            'total_views' => count($result->getAllViews()),
            'used_views' => count($result->getUsedViews()),
            'unused_views' => count($result->getUnusedViews()),
            'total_controllers' => $controllerMap->count(),
        ];

        /** @phpstan-ignore argument.type */
        return view('view-analyzer::index', [
            'controllerMap' => $controllerMap,
            'stats' => $stats,
            'unusedViews' => $result->getUnusedViews(),
        ]);
    }
}
