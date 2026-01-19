<?php

namespace LaravelViewAnalyzer\Parsers;

use LaravelViewAnalyzer\Parsers\Contracts\ParserInterface;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class PhpParser implements ParserInterface
{
    protected \PhpParser\Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    public function parse(string $content): array
    {
        try {
            $ast = $this->parser->parse($content);

            if ($ast === null) {
                return [];
            }

            $traverser = new NodeTraverser;
            $traverser->addVisitor(new NameResolver);

            $traverser->traverse($ast);

            return $ast;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
