<?php

namespace LaravelViewAnalyzer\Scanners;

use LaravelViewAnalyzer\Scanners\Contracts\ScannerInterface;

class FileScanner implements ScannerInterface
{
    public function __construct(
        protected string $filePath
    ) {}

    public function scan(): array
    {
        if (! file_exists($this->filePath) || ! is_readable($this->filePath)) {
            return [];
        }

        return [$this->filePath];
    }

    public function readContent(): ?string
    {
        if (! file_exists($this->filePath) || ! is_readable($this->filePath)) {
            return null;
        }

        return file_get_contents($this->filePath);
    }

    public function getLineNumber(int $position): int
    {
        $content = $this->readContent();
        if (! $content) {
            return 0;
        }

        return substr_count(substr($content, 0, $position), "\n") + 1;
    }

    /**
     * Attempts to find the method name surrounding a given position.
     */
    public function getMethodAtPosition(int $position): string
    {
        $content = $this->readContent();
        if (! $content) {
            return 'unknown';
        }

        // Look backwards for "function [name]"
        $before = substr($content, 0, $position);

        // Match the last function definition before the position
        if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/', $before, $matches, PREG_OFFSET_CAPTURE)) {
            $lastMatch = end($matches[1]);

            return $lastMatch[0];
        }

        return 'unknown';
    }
}
