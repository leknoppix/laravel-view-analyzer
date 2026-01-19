<?php

namespace LaravelViewAnalyzer\Commands;

use Illuminate\Console\Command;
use LaravelViewAnalyzer\Reports\ConsoleReporter;
use LaravelViewAnalyzer\Reports\CsvReporter;
use LaravelViewAnalyzer\Reports\HtmlReporter;
use LaravelViewAnalyzer\Reports\JsonReporter;
use LaravelViewAnalyzer\ViewAnalyzer;

class ViewsAnalyzeCommand extends Command
{
    protected $signature = 'views:analyze
                            {--format=table : Output format (table, json, html, csv)}
                            {--output= : Save output to file}
                            {--no-cache : Disable caching}
                            {--show-references : Show all references}';

    protected $description = 'Analyze view usage across the Laravel application';

    public function handle(): int
    {
        $this->info('Analyzing views...');

        $analyzer = new ViewAnalyzer(config('view-analyzer'));
        $result = $analyzer->analyze();

        $reporter = $this->getReporter($this->option('format'));
        $output = $reporter->generate($result);

        if ($outputPath = $this->option('output')) {
            file_put_contents($outputPath, $output);
            $this->info("Report saved to: {$outputPath}");
        } else {
            $this->line($output);
        }

        return self::SUCCESS;
    }

    protected function getReporter(string $format): mixed
    {
        return match ($format) {
            'json' => new JsonReporter,
            'html' => new HtmlReporter,
            'csv' => new CsvReporter,
            default => new ConsoleReporter,
        };
    }
}
