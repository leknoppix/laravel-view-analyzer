<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class CommandAnalyzer implements AnalyzerInterface
{
    protected ViewCallDetector $detector;

    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->detector = new ViewCallDetector();
    }

    public function analyze(): Collection
    {
        $references = collect();
        $scanPaths = $this->config['scan_paths'] ?? [];

        // Find Console paths if they exist in scan_paths
        $consolePaths = array_filter($scanPaths, function ($path) {
            return str_contains($path, 'Console');
        });

        // Fallback to default if none found and app_path exists
        if (empty($consolePaths) && function_exists('app_path')) {
            $defaultPath = app_path('Console');
            if (is_dir($defaultPath)) {
                $consolePaths[] = $defaultPath;
            }
        }

        $excludePaths = $this->config['exclude_paths'] ?? [];

        foreach ($consolePaths as $path) {
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
                    $methodName = $fileScanner->getMethodAtPosition($match['position']);

                    $references->push(new ViewReference(
                        viewName: $match['view'],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: "Command::{$methodName}",
                        type: 'command',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Command Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['command']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['command']['priority'] ?? 35;
    }
}
