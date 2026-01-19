<?php

namespace LaravelViewAnalyzer\Results;

class ViewReference
{
    public function __construct(
        public string $viewName,
        public string $sourceFile,
        public int $lineNumber,
        public string $context,
        public string $type,
        public bool $isDynamic = false
    ) {
    }

    public function toArray(): array
    {
        return [
            'view_name' => $this->viewName,
            'source_file' => $this->sourceFile,
            'line_number' => $this->lineNumber,
            'context' => $this->context,
            'type' => $this->type,
            'is_dynamic' => $this->isDynamic,
        ];
    }
}
