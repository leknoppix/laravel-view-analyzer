<?php

namespace LaravelViewAnalyzer\Reports\Contracts;

use LaravelViewAnalyzer\Results\AnalysisResult;

interface ReporterInterface
{
    public function generate(AnalysisResult $result): string;

    public function supports(string $format): bool;
}
