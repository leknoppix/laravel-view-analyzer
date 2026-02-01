<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class ProviderAnalyzer implements AnalyzerInterface
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

        if (empty($scanPaths)) {
            try {
                if (function_exists('app_path')) {
                    $scanPaths[] = $this->config['providers_path'] ?? app_path('Providers');
                }
            } catch (\Throwable $e) {
                // Fallback or ignore if app_path is not available or fails
            }
        }

        $excludePaths = $this->config['exclude_paths'] ?? [];

        foreach ($scanPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $scanner = new DirectoryScanner($path, '*.php', $excludePaths);
            $files = $scanner->scan();

            foreach ($files as $file) {
                // We specifically look for ServiceProvider files or files in Providers directory
                if (! str_ends_with($file, 'ServiceProvider.php') && ! str_contains($file, DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR)) {
                    continue;
                }

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
                        context: "ServiceProvider::{$methodName}",
                        type: 'provider',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Provider Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['provider']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['provider']['priority'] ?? 70;
    }
}
