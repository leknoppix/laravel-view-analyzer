<?php

namespace LaravelViewAnalyzer\Results;

use Illuminate\Support\Collection;

class ViewUsage
{
    public function __construct(
        public string $viewName,
        public Collection $references,
        public ?string $filePath = null,
        public int $referenceCount = 0,
        public array $types = []
    ) {
        if ($this->referenceCount === 0) {
            $this->referenceCount = $this->references->count();
        }

        if (empty($this->types)) {
            $this->types = $this->references->pluck('type')->unique()->values()->toArray();
        }
    }

    public function toArray(): array
    {
        return [
            'view_name' => $this->viewName,
            'file_path' => $this->filePath,
            'reference_count' => $this->referenceCount,
            'types' => $this->types,
            'references' => $this->references->map(fn ($ref) => $ref->toArray())->toArray(),
        ];
    }
}
