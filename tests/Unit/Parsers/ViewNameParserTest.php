<?php

namespace LaravelViewAnalyzer\Tests\Unit\Parsers;

use LaravelViewAnalyzer\Parsers\ViewNameParser;
use LaravelViewAnalyzer\Tests\TestCase;

class ViewNameParserTest extends TestCase
{
    public function test_it_converts_between_path_and_name()
    {
        $parser = new ViewNameParser('/var/www/resources/views');

        $name = $parser->filePathToViewName('/var/www/resources/views/pages/home.blade.php');
        $this->assertEquals('pages.home', $name);

        $path = $parser->viewNameToFilePath('pages.home');
        $this->assertStringContainsString('pages/home.blade.php', $path);

        $this->assertEquals([], $parser->parse('content'));
    }
}
