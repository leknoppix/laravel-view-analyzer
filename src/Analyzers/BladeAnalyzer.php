<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\BladeDirectiveDetector;
use LaravelViewAnalyzer\Detectors\ComponentTagDetector;
use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class BladeAnalyzer implements AnalyzerInterface
{
    protected BladeDirectiveDetector $directiveDetector;

    protected ViewCallDetector $callDetector;

    protected ComponentTagDetector $tagDetector;

    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->directiveDetector = new BladeDirectiveDetector();
        $this->callDetector = new ViewCallDetector();
        $this->tagDetector = new ComponentTagDetector();
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

                // Detect Blade directives (@include, etc.)
                $directiveMatches = $this->directiveDetector->detect($content);
                foreach ($directiveMatches as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match['position']);

                    $references->push(new ViewReference(
                        viewName: $match['view'],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: '@' . $match['directive'],
                        type: 'blade',
                        isDynamic: false
                    ));
                }

                // Detect tag-based components (<x-component />)
                $tagMatches = $this->tagDetector->detect($content);
                foreach ($tagMatches as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match['position']);

                    $references->push(new ViewReference(
                        viewName: $match['view'],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: '<x-' . $match['tag'] . '>',
                        type: 'blade',
                        isDynamic: false
                    ));
                }

                // Detect view calls (like $users->links('...'))
                $callMatches = $this->callDetector->detect($content);
                foreach ($callMatches as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match['position']);

                    $references->push(new ViewReference(
                        viewName: $match['view'],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: 'Blade call',
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
