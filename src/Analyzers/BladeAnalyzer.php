<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\BladeDirectiveDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class BladeAnalyzer implements AnalyzerInterface
{
    protected BladeDirectiveDetector $detector;

    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->detector = new BladeDirectiveDetector;
    }

    public function analyze(): Collection
    {
        $references = collect();
        $viewPaths = $this->config['view_paths'] ?? [resource_path('views')];
        $excludePaths = $this->config['exclude_paths'] ?? [];

        foreach ($viewPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $scanner = new DirectoryScanner($path, '*.blade.php', $excludePaths);
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
                        context: '@'.$match['directive'],
                        type: 'blade',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Blade Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['blade']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['blade']['priority'] ?? 20;
    }
}
