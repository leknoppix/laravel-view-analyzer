<?php

namespace LaravelViewAnalyzer\Results;

use Illuminate\Support\Collection;

class AnalysisResult
{
    public function __construct(
        public int $totalViews,
        public Collection $usedViews,
        public Collection $unusedViews,
        public Collection $dynamicViews,
        public array $statistics = [],
        public array $warnings = []
    ) {}

    public function getAllViews(): Collection
    {
        return $this->usedViews->merge($this->unusedViews);
    }

    public function getUsedViews(): Collection
    {
        return $this->usedViews;
    }

    public function getUnusedViews(): Collection
    {
        return $this->unusedViews;
    }

    public function toArray(): array
    {
        return [
            'total_views' => $this->totalViews,
            'used_views_count' => $this->usedViews->count(),
            'unused_views_count' => $this->unusedViews->count(),
            'dynamic_views_count' => $this->dynamicViews->count(),
            'statistics' => $this->statistics,
            'warnings' => $this->warnings,
            'used_views' => $this->usedViews->map(fn ($view) => $view->toArray())->toArray(),
            'unused_views' => $this->unusedViews->map(fn ($view) => $view->toArray())->toArray(),
        ];
    }
}
