<?php

namespace LaravelViewAnalyzer\Parsers;

use LaravelViewAnalyzer\Parsers\Contracts\ParserInterface;
use LaravelViewAnalyzer\Support\ViewPathResolver;

class ViewNameParser implements ParserInterface
{
    public function __construct(
        protected string $viewsPath
    ) {
    }

    public function parse(string $content): array
    {
        return [];
    }

    public function filePathToViewName(string $filePath): string
    {
        return ViewPathResolver::toViewName($filePath, $this->viewsPath);
    }

    public function viewNameToFilePath(string $viewName): string
    {
        return ViewPathResolver::toFilePath($viewName, $this->viewsPath);
    }
}
