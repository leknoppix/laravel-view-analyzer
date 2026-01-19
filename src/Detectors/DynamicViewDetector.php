<?php

namespace LaravelViewAnalyzer\Detectors;

class DynamicViewDetector
{
    public function detect(string $content): array
    {
        $dynamicViews = [];

        if (preg_match_all('/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $dynamicViews[] = [
                    'variable' => $match[0],
                    'value' => $match[1],
                ];
            }
        }

        return $dynamicViews;
    }

    public function getConfidenceScore(array $context): float
    {
        return 0.7;
    }
}
