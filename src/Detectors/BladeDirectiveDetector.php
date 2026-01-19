<?php

namespace LaravelViewAnalyzer\Detectors;

use LaravelViewAnalyzer\Support\PatternMatcher;

class BladeDirectiveDetector
{
    protected array $directives = [
        'extends',
        'include',
        'includeIf',
        'includeWhen',
        'includeUnless',
        'includeFirst',
        'component',
        'each',
    ];

    public function detect(string $content): array
    {
        $results = [];

        foreach ($this->directives as $directive) {
            $matches = PatternMatcher::matchBladeDirective($content, $directive);
            $results = array_merge($results, $matches);
        }

        return $results;
    }
}
