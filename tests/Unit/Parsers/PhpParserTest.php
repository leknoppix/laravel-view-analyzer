<?php

namespace LaravelViewAnalyzer\Tests\Unit\Parsers;

use LaravelViewAnalyzer\Parsers\PhpParser;
use LaravelViewAnalyzer\Tests\TestCase;

class PhpParserTest extends TestCase
{
    public function test_it_parses_php_content_into_ast()
    {
        $parser = new PhpParser();
        $content = '<?php echo "hello world";';

        $ast = $parser->parse($content);

        $this->assertIsArray($ast);
        $this->assertNotEmpty($ast);
        $this->assertInstanceOf(\PhpParser\Node\Stmt\Echo_::class, $ast[0]);
    }

    public function test_it_returns_empty_array_on_invalid_php()
    {
        $parser = new PhpParser();
        $content = '<?php this is not valid php syntax;';

        $ast = $parser->parse($content);

        $this->assertIsArray($ast);
        $this->assertEmpty($ast);
    }

    public function test_it_returns_empty_array_on_empty_content()
    {
        $parser = new PhpParser();
        $ast = $parser->parse('');

        $this->assertIsArray($ast);
        $this->assertEmpty($ast);
    }

    public function test_it_returns_empty_array_when_exception_occurs()
    {
        $parser = new PhpParser();

        // On injecte un parser qui lance une exception via réflexion
        $mockParser = \Mockery::mock(\PhpParser\Parser::class);
        $mockParser->shouldReceive('parse')->andThrow(new \Exception('Test exception'));

        $reflection = new \ReflectionClass($parser);
        $property = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($parser, $mockParser);

        $ast = $parser->parse('<?php some code');

        $this->assertIsArray($ast);
        $this->assertEmpty($ast);
    }

    public function test_it_returns_empty_array_when_parser_returns_null()
    {
        $parser = new PhpParser();

        $mockParser = \Mockery::mock(\PhpParser\Parser::class);
        $mockParser->shouldReceive('parse')->andReturn(null);

        $reflection = new \ReflectionClass($parser);
        $property = $reflection->getProperty('parser');
        $property->setAccessible(true);
        $property->setValue($parser, $mockParser);

        $this->assertEquals([], $parser->parse('<?php '));
    }
}
