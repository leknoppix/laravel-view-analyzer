<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class MiddlewareAnalyzer implements AnalyzerInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function analyze(): Collection
    {
        $references = collect();
        $middlewarePath = app_path('Http/Middleware');
        $excludePaths = $this->config['exclude_paths'] ?? [];

        if (! is_dir($middlewarePath)) {
            return $references;
        }

        $scanner = new DirectoryScanner($middlewarePath, '*.php', $excludePaths);
        $files = $scanner->scan();

        foreach ($files as $file) {
            $fileScanner = new FileScanner($file);
            $content = $fileScanner->readContent();

            if (! $content) {
                continue;
            }

            if (preg_match_all('/view\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match[1]);

                    $references->push(new ViewReference(
                        viewName: $match[0],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: 'Middleware',
                        type: 'middleware',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Middleware Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['middleware']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['middleware']['priority'] ?? 60;
    }
}
