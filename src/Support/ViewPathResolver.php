<?php

namespace LaravelViewAnalyzer\Support;

class ViewPathResolver
{
    public static function toViewName(string $filePath, string $viewsPath): string
    {
        $relativePath = PathHelper::makeRelative($filePath, $viewsPath);

        $relativePath = preg_replace('/\.blade\.php$/', '', $relativePath);

        return str_replace('/', '.', $relativePath);
    }

    public static function toFilePath(string $viewName, string $viewsPath): string
    {
        $relativePath = str_replace('.', '/', $viewName);

        return PathHelper::normalize($viewsPath . '/' . $relativePath . '.blade.php');
    }

    public static function resolveNamespace(string $viewName): array
    {
        if (str_contains($viewName, '::')) {
            [$namespace, $view] = explode('::', $viewName, 2);

            return ['namespace' => $namespace, 'view' => $view];
        }

        return ['namespace' => null, 'view' => $viewName];
    }
}
