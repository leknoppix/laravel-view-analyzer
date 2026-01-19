<?php

namespace LaravelViewAnalyzer\Scanners;

use LaravelViewAnalyzer\Scanners\Contracts\ScannerInterface;
use Symfony\Component\Finder\Finder;

class DirectoryScanner implements ScannerInterface
{
    protected array $excludePaths;

    protected string $pattern;

    protected int $maxDepth;

    public function __construct(
        protected string $directory,
        string $pattern = '*',
        array $excludePaths = [],
        int $maxDepth = 50
    ) {
        $this->pattern = $pattern;
        $this->excludePaths = $excludePaths;
        $this->maxDepth = $maxDepth;
    }

    public function scan(): array
    {
        if (! is_dir($this->directory)) {
            return [];
        }

        $finder = Finder::create()
            ->files()
            ->in($this->directory)
            ->name($this->pattern)
            ->depth('<= '.$this->maxDepth)
            ->ignoreUnreadableDirs()
            ->followLinks();

        foreach ($this->excludePaths as $excludePath) {
            $finder->notPath($excludePath);
            $finder->exclude($excludePath);
        }

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }
}
