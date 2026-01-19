<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class MailableAnalyzer implements AnalyzerInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function analyze(): Collection
    {
        $references = collect();
        $mailPath = app_path('Mail');
        $excludePaths = $this->config['exclude_paths'] ?? [];

        if (! is_dir($mailPath)) {
            return $references;
        }

        $scanner = new DirectoryScanner($mailPath, '*.php', $excludePaths);
        $files = $scanner->scan();

        foreach ($files as $file) {
            $fileScanner = new FileScanner($file);
            $content = $fileScanner->readContent();

            if (! $content) {
                continue;
            }

            if (preg_match_all('/new\s+Content\s*\([^)]*(?:html|view|markdown):\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match[1]);

                    $references->push(new ViewReference(
                        viewName: $match[0],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: 'Mailable (Content)',
                        type: 'mailable',
                        isDynamic: false
                    ));
                }
            }

            if (preg_match_all('/->(?:view|markdown)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match[1]);

                    $references->push(new ViewReference(
                        viewName: $match[0],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: 'Mailable (Legacy)',
                        type: 'mailable',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Mailable Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['mailable']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['mailable']['priority'] ?? 30;
    }
}
