<?php

namespace LaravelViewAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsUsedCommand extends Command
{
    protected $signature = 'views:used
                            {--type=all : Filter by type (controller, blade, mailable, all)}
                            {--sort=name : Sort by name or count}
                            {--min-references=1 : Minimum number of references}
                            {--show-locations : Show file paths and line numbers}';

    protected $description = 'List all used views with their references';

    public function handle(ViewAnalyzer $analyzer): int
    {
        $this->info('Finding used views...');

        $result = $analyzer->analyze();

        $usedViews = $result->usedViews;

        if ($this->option('type') !== 'all') {
            $usedViews = $usedViews->filter(function ($view) {
                return in_array($this->option('type'), $view->types);
            });
        }

        $minRefs = (int) $this->option('min-references');
        $usedViews = $usedViews->filter(fn ($view) => $view->referenceCount >= $minRefs);

        if ($this->option('sort') === 'count') {
            $usedViews = $usedViews->sortByDesc('referenceCount');
        } else {
            $usedViews = $usedViews->sortBy('viewName');
        }

        $this->line("\n<fg=green>Used Views ({$usedViews->count()} found)</>");
        $this->line(str_repeat('=', 50));

        foreach ($usedViews as $view) {
            $this->line("\n<fg=cyan>{$view->viewName}</> ({$view->referenceCount} references)");
            $this->line('  Types: ' . implode(', ', $view->types));

            if ($this->option('show-locations')) {
                foreach ($view->references->take(10) as $ref) {
                    $this->line("    - {$ref->sourceFile}:{$ref->lineNumber} ({$ref->context})");
                }
            }
        }

        return self::SUCCESS;
    }
}
