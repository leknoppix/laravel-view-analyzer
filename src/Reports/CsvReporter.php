<?php

namespace LaravelViewAnalyzer\Reports;

use LaravelViewAnalyzer\Reports\Contracts\ReporterInterface;
use LaravelViewAnalyzer\Results\AnalysisResult;

class CsvReporter implements ReporterInterface
{
    public function generate(AnalysisResult $result): string
    {
        $rows = [];
        $rows[] = ['View Name', 'Status', 'Reference Count', 'File Path', 'Types'];

        foreach ($result->usedViews as $view) {
            $rows[] = [
                $view->viewName,
                'Used',
                $view->referenceCount,
                '',
                implode(', ', $view->types),
            ];
        }

        foreach ($result->unusedViews as $view) {
            $rows[] = [
                $view->viewName,
                'Unused',
                0,
                $view->filePath,
                '',
            ];
        }

        $output = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    public function supports(string $format): bool
    {
        return $format === 'csv';
    }
}
