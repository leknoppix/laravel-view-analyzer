<?php

namespace LaravelViewAnalyzer\Reports;

use LaravelViewAnalyzer\Reports\Contracts\ReporterInterface;
use LaravelViewAnalyzer\Results\AnalysisResult;

class HtmlReporter implements ReporterInterface
{
    public function generate(AnalysisResult $result): string
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Analysis Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat { background: #f0f0f0; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-value { font-size: 32px; font-weight: bold; color: #007bff; }
        .stat-label { color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f9f9f9; }
        .badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: black; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel View Analysis Report</h1>
        <div class="summary">
HTML;

        $html .= sprintf('<div class="stat"><div class="stat-value">%d</div><div class="stat-label">Total Views</div></div>', $result->totalViews);
        $html .= sprintf('<div class="stat"><div class="stat-value">%d</div><div class="stat-label">Used Views</div></div>', $result->usedViews->count());
        $html .= sprintf('<div class="stat"><div class="stat-value">%d</div><div class="stat-label">Unused Views</div></div>', $result->unusedViews->count());

        $html .= '</div><h2>Used Views</h2><table><tr><th>View Name</th><th>References</th><th>Types</th></tr>';

        foreach ($result->usedViews->take(100) as $view) {
            $html .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>%s</td></tr>',
                htmlspecialchars($view->viewName),
                $view->referenceCount,
                implode(', ', array_map(fn ($t) => '<span class="badge badge-success">' . htmlspecialchars($t) . '</span>', $view->types))
            );
        }

        $html .= '</table></div></body></html>';

        return $html;
    }

    public function supports(string $format): bool
    {
        return $format === 'html';
    }
}
