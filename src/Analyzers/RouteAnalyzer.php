<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class RouteAnalyzer implements AnalyzerInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function analyze(): Collection
    {
        $references = collect();
        $routesPath = base_path('routes');
        $excludePaths = $this->config['exclude_paths'] ?? [];

        if (! is_dir($routesPath)) {
            return $references;
        }

        $scanner = new DirectoryScanner($routesPath, '*.php', $excludePaths);
        $files = $scanner->scan();

        foreach ($files as $file) {
            $fileScanner = new FileScanner($file);
            $content = $fileScanner->readContent();

            if (! $content) {
                continue;
            }

            if (preg_match_all('/Route::view\s*\([^,]+,\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match[1]);

                    $references->push(new ViewReference(
                        viewName: $match[0],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: 'Route::view()',
                        type: 'route',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Route Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['route']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['route']['priority'] ?? 50;
    }
}
