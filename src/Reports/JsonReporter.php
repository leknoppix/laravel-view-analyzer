<?php

namespace LaravelViewAnalyzer\Reports;

use LaravelViewAnalyzer\Reports\Contracts\ReporterInterface;
use LaravelViewAnalyzer\Results\AnalysisResult;

class JsonReporter implements ReporterInterface
{
    public function generate(AnalysisResult $result): string
    {
        return json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function supports(string $format): bool
    {
        return $format === 'json';
    }
}
