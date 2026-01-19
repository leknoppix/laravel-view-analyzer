<?php

namespace LaravelViewAnalyzer\Reports;

use LaravelViewAnalyzer\Reports\Contracts\ReporterInterface;
use LaravelViewAnalyzer\Results\AnalysisResult;

class ConsoleReporter implements ReporterInterface
{
    public function generate(AnalysisResult $result): string
    {
        $output = [];

        $output[] = "\n===========================================";
        $output[] = 'Laravel View Analyzer - Analysis Report';
        $output[] = "===========================================\n";

        $output[] = 'Views Summary:';
        $output[] = sprintf('  Total Views Found: %d', $result->totalViews);
        $output[] = sprintf('  Used Views: %d', $result->usedViews->count());
        $output[] = sprintf('  Unused Views: %d', $result->unusedViews->count());
        $output[] = sprintf('  Dynamic/Uncertain: %d', $result->dynamicViews->count());
        $output[] = '';

        if (! empty($result->statistics)) {
            $output[] = 'Statistics:';
            foreach ($result->statistics as $key => $value) {
                $displayValue = is_array($value) ? json_encode($value) : $value;
                $output[] = sprintf('  %s: %s', ucfirst(str_replace('_', ' ', $key)), $displayValue);
            }
            $output[] = '';
        }

        if ($result->warnings) {
            $output[] = 'Warnings:';
            foreach ($result->warnings as $warning) {
                $output[] = '  - '.$warning;
            }
            $output[] = '';
        }

        return implode("\n", $output);
    }

    public function supports(string $format): bool
    {
        return in_array($format, ['console', 'table', 'text']);
    }
}
