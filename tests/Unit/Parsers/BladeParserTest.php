<?php

namespace LaravelViewAnalyzer\Tests\Unit\Parsers;

use LaravelViewAnalyzer\Parsers\BladeParser;
use LaravelViewAnalyzer\Tests\TestCase;

class BladeParserTest extends TestCase
{
    public function test_it_parses_blade_directives()
    {
        $parser = new BladeParser();
        $content = '@include("test.view") @extends("layout")';

        $results = $parser->parse($content);

        $this->assertCount(2, $results);

        // Les directives sont traitées dans l'ordre du tableau $directives : extends puis include
        $this->assertEquals('layout', $results[0]['view']);
        $this->assertEquals('extends', $results[0]['directive']);

        $this->assertEquals('test.view', $results[1]['view']);
        $this->assertEquals('include', $results[1]['directive']);
    }
}
