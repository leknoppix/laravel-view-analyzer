<?php

namespace LaravelViewAnalyzer\Detectors;

use LaravelViewAnalyzer\Support\PatternMatcher;

class ComponentTagDetector
{
    public function detect(string $content): array
    {
        return PatternMatcher::matchComponentTag($content);
    }
}
