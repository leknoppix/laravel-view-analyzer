<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class ControllerAnalyzer implements AnalyzerInterface
{
    protected ViewCallDetector $detector;

    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->detector = new ViewCallDetector;
    }

    public function analyze(): Collection
    {
        $references = collect();
        $controllerPaths = $this->config['scan_paths'] ?? [app_path('Http/Controllers')];
        $excludePaths = $this->config['exclude_paths'] ?? [];

        foreach ($controllerPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $scanner = new DirectoryScanner($path, '*.php', $excludePaths);
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

                    $references->push(new ViewReference(
                        viewName: $match['view'],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: 'Controller',
                        type: 'controller',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Controller Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['controller']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['controller']['priority'] ?? 10;
    }
}
