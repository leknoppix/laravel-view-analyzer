<?php

namespace LaravelViewAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsUnusedCommand extends Command
{
    protected $signature = 'views:unused
                            {--path= : Filter by specific path}
                            {--size : Show file sizes}
                            {--suggest-delete : Generate delete commands}';

    protected $description = 'List all unused/orphaned views';

    public function handle(ViewAnalyzer $analyzer): int
    {
        $this->info('Finding unused views...');

        $result = $analyzer->analyze();

        $unusedViews = $result->unusedViews;

        if ($path = $this->option('path')) {
            $unusedViews = $unusedViews->filter(function ($view) use ($path) {
                return str_contains($view->filePath, $path);
            });
        }

        $unusedViews = $unusedViews->sortBy('viewName');

        $this->line("\n<fg=yellow>Unused Views ({$unusedViews->count()} found)</>");
        $this->line(str_repeat('=', 50));

        foreach ($unusedViews as $view) {
            $this->line("\n<fg=red>{$view->viewName}</>");
            $this->line("  Path: {$view->filePath}");

            if ($this->option('size')) {
                $this->line("  Size: {$view->toArray()['file_size_human']}");
            }

            $this->line("  Last Modified: {$view->lastModified->diffForHumans()}");

            if ($this->option('suggest-delete')) {
                $this->line("  <fg=gray>rm \"{$view->filePath}\"</>");
            }
        }

        if ($unusedViews->isEmpty()) {
            $this->info("\nNo unused views found!");
        }

        return self::SUCCESS;
    }
}
