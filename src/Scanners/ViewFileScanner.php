<?php

namespace LaravelViewAnalyzer\Scanners;

use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Scanners\Contracts\ScannerInterface;
use LaravelViewAnalyzer\Support\ViewPathResolver;

class ViewFileScanner implements ScannerInterface
{
    protected array $viewPaths;

    protected array $excludePaths;

    public function __construct(array $viewPaths, array $excludePaths = [])
    {
        $this->viewPaths = $viewPaths;
        $this->excludePaths = $excludePaths;
    }

    public function scan(): array
    {
        $views = [];

        foreach ($this->viewPaths as $viewPath) {
            $scanner = new DirectoryScanner(
                $viewPath,
                '*.blade.php',
                $this->excludePaths
            );

            $files = $scanner->scan();

            foreach ($files as $file) {
                $viewName = ViewPathResolver::toViewName($file, $viewPath);
                $views[$viewName] = $file;
            }
        }

        return $views;
    }

    public function getViewRegistry(): Collection
    {
        return collect($this->scan());
    }
}
