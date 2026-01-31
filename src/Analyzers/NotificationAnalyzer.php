<?php

namespace LaravelViewAnalyzer\Analyzers;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Detectors\ViewCallDetector;
use LaravelViewAnalyzer\Results\ViewReference;
use LaravelViewAnalyzer\Scanners\DirectoryScanner;
use LaravelViewAnalyzer\Scanners\FileScanner;

class NotificationAnalyzer implements AnalyzerInterface
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

        $scanPaths = $this->config['scan_paths'] ?? [];
        $notificationPaths = array_filter($scanPaths, function ($path) {
            return str_contains($path, 'Notifications');
        });

        if (empty($notificationPaths) && function_exists('app_path')) {
            $defaultPath = app_path('Notifications');
            if (is_dir($defaultPath)) {
                $notificationPaths[] = $defaultPath;
            }
        }

        $excludePaths = $this->config['exclude_paths'] ?? [];

        foreach ($notificationPaths as $path) {
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

                // Notifications often use ->view('...') or ->markdown('...') on MailMessage
                // ViewCallDetector already detects these patterns thanks to PatternMatcher updates
                $matches = $this->detector->detect($content);

                foreach ($matches as $match) {
                    $lineNumber = $fileScanner->getLineNumber($match['position']);
                    $methodName = $fileScanner->getMethodAtPosition($match['position']);

                    $references->push(new ViewReference(
                        viewName: $match['view'],
                        sourceFile: $file,
                        lineNumber: $lineNumber,
                        context: "Notification::{$methodName}",
                        type: 'notification',
                        isDynamic: false
                    ));
                }
            }
        }

        return $references;
    }

    public function getName(): string
    {
        return 'Notification Analyzer';
    }

    public function isEnabled(): bool
    {
        return $this->config['analyzers']['notification']['enabled'] ?? true;
    }

    public function getPriority(): int
    {
        return $this->config['analyzers']['notification']['priority'] ?? 32;
    }
}
