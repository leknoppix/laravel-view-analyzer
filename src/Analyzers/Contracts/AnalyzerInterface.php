<?php

namespace LaravelViewAnalyzer\Analyzers\Contracts;

use Illuminate\Support\Collection;

interface AnalyzerInterface
{
    public function analyze(): Collection;

    public function getName(): string;

    public function isEnabled(): bool;

    public function getPriority(): int;
}
