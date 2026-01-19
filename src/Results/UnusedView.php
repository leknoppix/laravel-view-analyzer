<?php

namespace LaravelViewAnalyzer\Results;

use Carbon\Carbon;

class UnusedView
{
    public function __construct(
        public string $viewName,
        public string $filePath,
        public int $fileSize,
        public Carbon $lastModified,
        public array $suggestions = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'view_name' => $this->viewName,
            'file_path' => $this->filePath,
            'file_size' => $this->fileSize,
            'file_size_human' => $this->getHumanFileSize(),
            'last_modified' => $this->lastModified->toDateTimeString(),
            'suggestions' => $this->suggestions,
        ];
    }

    protected function getHumanFileSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->fileSize;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }
}
