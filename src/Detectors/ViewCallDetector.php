<?php

namespace LaravelViewAnalyzer\Detectors;

use LaravelViewAnalyzer\Support\PatternMatcher;

class ViewCallDetector
{
    public function detect(string $content): array
    {
        return PatternMatcher::matchViewCall($content);
    }

    public function detectDynamic(string $content): array
    {
        $patterns = [
            '/view\s*\(\s*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
            '/View::make\s*\(\s*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
        ];

        $dynamicViews = [];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $dynamicViews[] = [
                        'pattern' => $match[0],
                        'position' => $match[1],
                    ];
                }
            }
        }

        return $dynamicViews;
    }
}
