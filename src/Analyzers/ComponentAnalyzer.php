<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class ComponentAnalyzer implements AnalyzerInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function analyze(): Collection
    {
        $references = collect();
        $componentPath = app_path('View/Components');
        $excludePaths = $this->config['exclude_paths'] ?? [];

        if (is_dir($componentPath)) {
            $scanner = new DirectoryScanner($componentPath, '*.php', $excludePaths);
            $files = $scanner->scan();

            foreach ($files as $file) {
                $fileScanner = new FileScanner($file);
                $content = $fileScanner->readContent();

                if (! $content) {
                    continue;
                }

                if (preg_match_all('/return\s+view\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[1] as $match) {
                        $lineNumber = $fileScanner->getLineNumber($match[1]);

                        $references->push(new ViewReference(
                            viewName: $match[0],
                            sourceFile: $file,
                            lineNumber: $lineNumber,
                            context: 'Component render()',
                            type: 'component',
                            isDynamic: false
                        ));
                    }
                }
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
