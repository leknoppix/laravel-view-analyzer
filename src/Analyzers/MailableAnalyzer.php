<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class MailableAnalyzer implements AnalyzerInterface
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
        $mailPath = $this->config['mail_path'] ?? app_path('Mail');
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

            // Detect new Content(...) syntax (Laravel 9+)
            // Matches: new Content(view: 'view.name'), new Content('view.name'), new Content(markdown: 'view.name')
            if (preg_match_all('/new\s+Content\s*\(\s*(?:(?:html|view|markdown):\s*)?[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
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

            // Detect legacy ->view() and ->markdown() calls
            $matches = $this->detector->detect($content);
            foreach ($matches as $match) {
                $lineNumber = $fileScanner->getLineNumber($match['position']);
                $methodName = $fileScanner->getMethodAtPosition($match['position']);

                $references->push(new ViewReference(
                    viewName: $match['view'],
                    sourceFile: $file,
                    lineNumber: $lineNumber,
                    context: "Mailable (Legacy)::{$methodName}",
                    type: 'mailable',
                    isDynamic: false
                ));
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
