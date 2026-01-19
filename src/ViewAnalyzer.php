<?php

namespace LaravelViewAnalyzer;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use LaravelViewAnalyzer\Analyzers\BladeAnalyzer;
use LaravelViewAnalyzer\Analyzers\ComponentAnalyzer;
use LaravelViewAnalyzer\Analyzers\Contracts\AnalyzerInterface;
use LaravelViewAnalyzer\Analyzers\ControllerAnalyzer;
use LaravelViewAnalyzer\Analyzers\MailableAnalyzer;
use LaravelViewAnalyzer\Analyzers\MiddlewareAnalyzer;
use LaravelViewAnalyzer\Analyzers\RouteAnalyzer;
use LaravelViewAnalyzer\Results\AnalysisResult;
use LaravelViewAnalyzer\Results\UnusedView;
use LaravelViewAnalyzer\Results\ViewUsage;
use LaravelViewAnalyzer\Scanners\ViewFileScanner;

class ViewAnalyzer
{
    protected array $config;

    protected Collection $analyzers;

    protected ?Collection $viewRegistry = null;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('view-analyzer', []);
        $this->analyzers = collect();
        $this->registerDefaultAnalyzers();
    }

    protected function registerDefaultAnalyzers(): void
    {
        $this->addAnalyzer(new ControllerAnalyzer($this->config));
        $this->addAnalyzer(new BladeAnalyzer($this->config));
        $this->addAnalyzer(new MailableAnalyzer($this->config));
        $this->addAnalyzer(new ComponentAnalyzer($this->config));
        $this->addAnalyzer(new RouteAnalyzer($this->config));
        $this->addAnalyzer(new MiddlewareAnalyzer($this->config));
    }

    public function addAnalyzer(AnalyzerInterface $analyzer): self
    {
        $this->analyzers->push($analyzer);

        return $this;
    }

    public function analyze(): AnalysisResult
    {
        $viewRegistry = $this->getViewRegistry();
        $allReferences = collect();

        $enabledAnalyzers = $this->analyzers
            ->filter(fn ($analyzer) => $analyzer->isEnabled())
            ->sortBy(fn ($analyzer) => $analyzer->getPriority());

        foreach ($enabledAnalyzers as $analyzer) {
            $references = $analyzer->analyze();
            $allReferences = $allReferences->merge($references);
        }

        $usedViews = $this->aggregateUsedViews($allReferences);
        $unusedViews = $this->findUnusedViews($viewRegistry, $usedViews);
        $dynamicViews = $allReferences->where('isDynamic', true);

        $statistics = $this->generateStatistics($allReferences);

        return new AnalysisResult(
            totalViews: $viewRegistry->count(),
            usedViews: $usedViews,
            unusedViews: $unusedViews,
            dynamicViews: $dynamicViews,
            statistics: $statistics
        );
    }

    public function findUsedViews(): Collection
    {
        return $this->analyze()->usedViews;
    }

    public function findUnusedViews(Collection $viewRegistry, Collection $usedViews): Collection
    {
        $usedViewNames = $usedViews->pluck('viewName')->unique();

        return $viewRegistry
            ->filter(fn ($filePath, $viewName) => ! $usedViewNames->contains($viewName))
            ->map(function ($filePath, $viewName) {
                return new UnusedView(
                    viewName: $viewName,
                    filePath: $filePath,
                    fileSize: file_exists($filePath) ? filesize($filePath) : 0,
                    lastModified: file_exists($filePath) ? Carbon::createFromTimestamp(filemtime($filePath)) : now()
                );
            })
            ->values();
    }

    protected function aggregateUsedViews(Collection $references): Collection
    {
        return $references
            ->groupBy('viewName')
            ->map(fn ($refs, $viewName) => new ViewUsage(
                viewName: $viewName,
                references: $refs
            ))
            ->values();
    }

    protected function getViewRegistry(): Collection
    {
        if ($this->viewRegistry !== null) {
            return $this->viewRegistry;
        }

        $scanner = new ViewFileScanner(
            $this->config['view_paths'] ?? [resource_path('views')],
            $this->config['exclude_paths'] ?? []
        );

        $this->viewRegistry = $scanner->getViewRegistry();

        return $this->viewRegistry;
    }

    public function getStatistics(): array
    {
        return $this->generateStatistics(collect());
    }

    protected function generateStatistics(Collection $references): array
    {
        $byType = $references->groupBy('type')->map->count();

        return [
            'total_references' => $references->count(),
            'by_type' => $byType->toArray(),
        ];
    }
}
