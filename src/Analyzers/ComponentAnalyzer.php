<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class ComponentAnalyzer implements AnalyzerInterface
{
    protected array $config;

    protected ViewCallDetector $detector;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->detector = new ViewCallDetector();
    }

    public function analyze(): Collection
    {
        $references = collect();
        $componentPath = $this->config['component_path'] ?? app_path('View/Components');
        $excludePaths = $this->config['exclude_paths'] ?? [];

        if (! is_dir($componentPath)) {
            return $references;
        }

        $scanner = new DirectoryScanner($componentPath, '*.php', $excludePaths);
        $files = $scanner->scan();

        foreach ($files as $file) {
            $fileScanner = new FileScanner($file);
            $content = $fileScanner->readContent();

            if (! $content) {
                continue;
            }

            $matches = $this->detector->detect($content);

            foreach ($matches as $match) {
                $lineNumber = $fileScanner->getLineNumber($match['position']);
                $methodName = $fileScanner->getMethodAtPosition($match['position']);

                $references->push(new ViewReference(
                    viewName: $match['view'],
                    sourceFile: $file,
                    lineNumber: $lineNumber,
                    context: "Component::{$methodName}",
                    type: 'component',
                    isDynamic: false
                ));
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Component Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['component']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['component']['priority'] ?? 40;
    }
}
