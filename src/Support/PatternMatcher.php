<?php

namespace LaravelViewAnalyzer\Support;

class PatternMatcher
{
    public static function matchViewCall(string $content): array
    {
        $patterns = [
            '/view\s*\(\s*[\'"]([^\'"\)]+)[\'"]\s*[\),]/',
            '/View::make\s*\(\s*[\'"]([^\'"\)]+)[\'"]\s*[\),]/',
            '/response\(\)->view\s*\(\s*[\'"]([^\'"\)]+)[\'"]\s*[\),]/',
        ];

        $matches = [];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE)) {
                foreach ($found[1] as $match) {
                    $matches[] = [
                        'view' => $match[0],
                        'position' => $match[1],
                    ];
                }
            }
        }

        return $matches;
    }

    public static function matchBladeDirective(string $content, string $directive): array
    {
        $pattern = '/@'.$directive.'\s*\(\s*[\'"]([^\'"\)]+)[\'"]/';
        $matches = [];

        if (preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE)) {
            foreach ($found[1] as $match) {
                $matches[] = [
                    'view' => $match[0],
                    'position' => $match[1],
                    'directive' => $directive,
                ];
            }
        }

        return $matches;
    }

    public static function shouldExclude(string $path, array $excludePaths): bool
    {
        $normalizedPath = PathHelper::normalize($path);

        foreach ($excludePaths as $exclude) {
            $exclude = PathHelper::normalize($exclude);
            if (str_contains($normalizedPath, '/'.$exclude.'/') || str_ends_with($normalizedPath, '/'.$exclude)) {
                return true;
            }
        }

        return false;
    }
}
