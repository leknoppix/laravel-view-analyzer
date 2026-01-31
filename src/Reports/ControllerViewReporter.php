<?php

namespace LaravelViewAnalyzer\Reports;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\BufferedOutput;

class ControllerViewReporter
{
    public function __construct() {}

    public function generateTable(Collection $controllerMap, bool $groupByNamespace = false): string
    {
        $bufferedOutput = new BufferedOutput();
        $table = new Table($bufferedOutput);
        $table->setHeaders(['Controller', 'Action', 'Views Loaded']);

        if ($groupByNamespace) {
            $grouped = $controllerMap->groupBy('namespace')->sortKeys();

            foreach ($grouped as $namespace => $controllers) {
                // Namespace header
                $table->addRow([
                    "<fg=yellow>Namespace: {$namespace}</>",
                    '',
                    '',
                ]);
                $table->addRow(new TableSeparator());

                $this->addControllerRows($table, $controllers);
                $table->addRow(new TableSeparator());
            }
        } else {
            $this->addControllerRows($table, $controllerMap);
        }

        $table->render();

        return $bufferedOutput->fetch();
    }

    private function addControllerRows(Table $table, Collection $controllers): void
    {
        foreach ($controllers as $controller) {
            $firstAction = true;

            if (empty($controller['actions'])) {
                $table->addRow([
                    "<fg=cyan>{$controller['controller']}</>",
                    '<fg=gray>(no actions)</>',
                    '',
                ]);

                continue;
            }

            foreach ($controller['actions'] as $action) {
                $views = $action['views'];
                $viewsDisplay = empty($views)
                    ? '<fg=gray>(no views)</>'
                    : implode("\n", array_map(fn ($v) => "  • {$v}", $views));

                $table->addRow([
                    $firstAction ? "<fg=cyan>{$controller['controller']}</>" : '',
                    "<fg=green>{$action['action']}</>",
                    $viewsDisplay,
                ]);

                $firstAction = false;
            }

            $table->addRow(new TableSeparator());
        }
    }

    public function generateTree(Collection $controllerMap): string
    {
        $bufferedOutput = new BufferedOutput();
        $grouped = $controllerMap->groupBy('namespace')->sortKeys();

        foreach ($grouped as $namespace => $controllers) {
            $bufferedOutput->writeln("<fg=yellow;options=bold>📦 {$namespace}</>");

            foreach ($controllers as $controller) {
                $bufferedOutput->writeln("  <fg=cyan>├─ 🎮 {$controller['controller']}</>");

                if (empty($controller['actions'])) {
                    $bufferedOutput->writeln('  <fg=gray>│    └─ (no actions)</>');

                    continue;
                }

                $actionCount = count($controller['actions']);
                $i = 0;

                foreach ($controller['actions'] as $action) {
                    $i++;
                    $isLastAction = ($i === $actionCount);
                    $prefix = $isLastAction ? '└─' : '├─';
                    $treeBar = $isLastAction ? '   ' : '│  ';

                    $bufferedOutput->writeln("  <fg=gray>│</>  <fg=green>{$prefix} ⚡ {$action['action']}</>");

                    if (! empty($action['views'])) {
                        $viewCount = count($action['views']);
                        $j = 0;
                        foreach ($action['views'] as $view) {
                            $j++;
                            $isLastView = ($j === $viewCount);
                            $viewPrefix = $isLastView ? '└─' : '├─';

                            $bufferedOutput->writeln("  <fg=gray>│    {$treeBar} {$viewPrefix} 👁️  {$view}</>");
                        }
                    }
                }
            }
            $bufferedOutput->writeln('');
        }

        return $bufferedOutput->fetch();
    }

    public function generateJson(Collection $controllerMap, bool $groupByNamespace = false): string
    {
        $data = $groupByNamespace
            ? $controllerMap->groupBy('namespace')->toArray()
            : $controllerMap->toArray();

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function generateCsv(Collection $controllerMap): string
    {
        $csv = "Controller,Namespace,Action,View,File Path\n";

        foreach ($controllerMap as $controller) {
            foreach ($controller['actions'] as $action) {
                if (empty($action['views'])) {
                    $csv .= sprintf(
                        "%s,%s,%s,%s,%s\n",
                        $this->escapeCsv($controller['controller']),
                        $this->escapeCsv($controller['namespace']),
                        $this->escapeCsv($action['action']),
                        '(no views)',
                        $this->escapeCsv($controller['file_path'])
                    );
                } else {
                    foreach ($action['views'] as $view) {
                        $csv .= sprintf(
                            "%s,%s,%s,%s,%s\n",
                            $this->escapeCsv($controller['controller']),
                            $this->escapeCsv($controller['namespace']),
                            $this->escapeCsv($action['action']),
                            $this->escapeCsv($view),
                            $this->escapeCsv($controller['file_path'])
                        );
                    }
                }
            }
        }

        return $csv;
    }

    private function escapeCsv(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }
}
