<?php

namespace LaravelViewAnalyzer\Parsers\Contracts;

interface ParserInterface
{
    public function parse(string $content): array;
}
