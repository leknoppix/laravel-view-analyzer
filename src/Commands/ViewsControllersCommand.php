<?php

namespace LaravelViewAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelViewAnalyzer\Reports\ControllerViewReporter;
use LaravelViewAnalyzer\Support\ControllerViewMapper;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsControllersCommand extends Command
{
    protected $signature = 'views:controllers
                            {--controller= : Filter by specific controller name}
                            {--group-by-namespace : Group controllers by namespace}
                            {--include-empty : Include methods without views}
                            {--format=table : Output format (table, json, csv, tree)}
                            {--output= : Output file path}';

    protected $description = 'List controllers with their actions and loaded views';

    public function handle(ViewAnalyzer $analyzer): int
    {
        $this->info('Analyzing controllers and their views...');

        $result = $analyzer->analyze();

        $mapper = new ControllerViewMapper($result);
        $controllerMap = $mapper->mapControllersToViews();

        // Apply filters
        if ($controller = $this->option('controller')) {
            $controllerMap = $controllerMap->filter(function ($item) use ($controller) {
                return str_contains($item['controller'], $controller);
            });
        }

        if (! $this->option('include-empty')) {
            $controllerMap = $controllerMap->filter(function ($item) {
                return count($item['views']) > 0;
            });
        }

        // Generate report
        $reporter = new ControllerViewReporter();
        $format = $this->option('format');

        $output = match ($format) {
            'json' => $reporter->generateJson($controllerMap, $this->option('group-by-namespace')),
            'csv' => $reporter->generateCsv($controllerMap),
            'tree' => $reporter->generateTree($controllerMap),
            default => $reporter->generateTable($controllerMap, $this->option('group-by-namespace')),
        };

        // Output to file or console
        if ($outputPath = $this->option('output')) {
            file_put_contents($outputPath, $output);
            $this->info("Report saved to: {$outputPath}");
        } else {
            foreach (explode("\n", $output) as $line) {
                $this->line($line);
            }
        }

        $this->newLine();
        $this->info("Total controllers: {$controllerMap->count()}");
        $totalActions = $controllerMap->sum(fn ($item) => count($item['actions']));
        $this->info("Total actions: {$totalActions}");

        return self::SUCCESS;
    }
}
