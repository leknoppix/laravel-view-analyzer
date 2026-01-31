<?php

namespace LaravelViewAnalyzer\Support;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Results\AnalysisResult;

class ControllerViewMapper
{
    public function __construct(
        private AnalysisResult $result
    ) {}

    public function mapControllersToViews(): Collection
    {
        $controllerMap = collect();

        // Get all controller references
        $controllerReferences = $this->result->usedViews
            ->flatMap(fn ($viewUsage) => $viewUsage->references)
            ->filter(fn ($ref) => $ref->type === 'controller')
            ->groupBy(fn ($ref) => $this->extractControllerName($ref->sourceFile));

        foreach ($controllerReferences as $controller => $references) {
            $actionMap = $references
                ->groupBy(fn ($ref) => $this->extractActionName($ref->context))
                ->map(function ($actionRefs, $action) {
                    return [
                        'action' => $action,
                        'views' => $actionRefs->pluck('viewName')->unique()->values()->toArray(),
                        'line_numbers' => $actionRefs->pluck('lineNumber')->unique()->values()->toArray(),
                    ];
                });

            $controllerMap->push([
                'controller' => $controller,
                'namespace' => $this->extractNamespace($controller),
                'file_path' => $references->first()->sourceFile,
                'actions' => $actionMap->values()->toArray(),
                'views' => $actionMap->flatMap(fn ($a) => $a['views'])->unique()->values()->toArray(),
            ]);
        }

        return $controllerMap->sortBy('controller')->values();
    }

    private function extractControllerName(string $filePath): string
    {
        // Extract controller name from file path
        // app/Http/Controllers/PostController.php -> PostController
        // app/Http/Controllers/Admin/PostController.php -> Admin\PostController
        $relativePath = str_replace(app_path('Http/Controllers/'), '', $filePath);
        $relativePath = str_replace('.php', '', $relativePath);

        return str_replace('/', '\\', $relativePath);
    }

    private function extractActionName(string $context): string
    {
        // Extract method name from context like "PostController::index"
        if (preg_match('/::([\w]+)/', $context, $matches)) {
            return $matches[1];
        }

        return 'unknown';
    }

    private function extractNamespace(string $controller): string
    {
        // Admin\PostController -> Admin
        // PostController -> (root)
        $parts = explode('\\', $controller);
        array_pop($parts);

        return empty($parts) ? '(root)' : implode('\\', $parts);
    }
}
