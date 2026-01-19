<?php

namespace LaravelViewAnalyzer\Scanners\Contracts;

interface ScannerInterface
{
    public function scan(): array;
}
