<?php

namespace LaravelViewAnalyzer\Parsers;

use LaravelViewAnalyzer\Parsers\Contracts\ParserInterface;
use LaravelViewAnalyzer\Support\PatternMatcher;

class BladeParser implements ParserInterface
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

    public function parse(string $content): array
    {
        $results = [];

        foreach ($this->directives as $directive) {
            $matches = PatternMatcher::matchBladeDirective($content, $directive);

            foreach ($matches as $match) {
                $results[] = [
                    'view' => $match['view'],
                    'directive' => $match['directive'],
                    'position' => $match['position'],
                ];
            }
        }

        return $results;
    }
}
