<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\SignatureParser;
use Lukman\Console\Exception\InvalidCommandException;

class SignatureParserTest extends TestCase
{
    private SignatureParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SignatureParser();
    }

    public function testParseCommandNameOnly(): void
    {
        $definition = $this->parser->parse('greet');
        $this->assertEquals('greet', $definition->name());
        $this->assertEmpty($definition->arguments());
        $this->assertEmpty($definition->options());
    }

    public function testRequiredArgument(): void
    {
        $definition = $this->parser->parse('greet {name}');
        $this->assertEquals('greet', $definition->name());
        
        $arg = $definition->argument('name');
        $this->assertNotNull($arg);
        $this->assertEquals('name', $arg['name']);
        $this->assertTrue($arg['required']);
        $this->assertNull($arg['default']);
    }

    public function testOptionalArgument(): void
    {
        $definition = $this->parser->parse('greet {name?}');
        
        $arg = $definition->argument('name');
        $this->assertNotNull($arg);
        $this->assertEquals('name', $arg['name']);
        $this->assertFalse($arg['required']);
        $this->assertNull($arg['default']);
    }

    public function testArgumentDefault(): void
    {
        $definition = $this->parser->parse('greet {name=Guest}');
        
        $arg = $definition->argument('name');
        $this->assertNotNull($arg);
        $this->assertEquals('name', $arg['name']);
        $this->assertFalse($arg['required']);
        $this->assertEquals('Guest', $arg['default']);
    }

    public function testFlagOption(): void
    {
        $definition = $this->parser->parse('make:user {--force}');
        
        $opt = $definition->option('force');
        $this->assertNotNull($opt);
        $this->assertEquals('force', $opt['name']);
        $this->assertFalse($opt['value_required']);
        $this->assertFalse($opt['default']);
    }

    public function testOptionValueRequired(): void
    {
        $definition = $this->parser->parse('make:user {--path=}');
        
        $opt = $definition->option('path');
        $this->assertNotNull($opt);
        $this->assertEquals('path', $opt['name']);
        $this->assertTrue($opt['value_required']);
        $this->assertNull($opt['default']);
    }

    public function testOptionDefault(): void
    {
        $definition = $this->parser->parse('make:user {--path=app}');
        
        $opt = $definition->option('path');
        $this->assertNotNull($opt);
        $this->assertEquals('path', $opt['name']);
        $this->assertTrue($opt['value_required']);
        $this->assertEquals('app', $opt['default']);
    }

    public function testMultipleArgumentAndOption(): void
    {
        $definition = $this->parser->parse('make:user {name} {--force} {--path=app}');
        $this->assertEquals('make:user', $definition->name());
        
        $this->assertNotNull($definition->argument('name'));
        $this->assertNotNull($definition->option('force'));
        $this->assertNotNull($definition->option('path'));
        $this->assertSame(['name'], array_keys($definition->arguments()));
        $this->assertSame(['force', 'path'], array_keys($definition->options()));
    }

    public function testInvalidEmptySignature(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('');
    }

    public function testInvalidEmptySignatureWhitespace(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('   ');
    }

    public function testInvalidTokenWithoutBraces(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('greet name');
    }

    public function testInvalidTokenMissingBrace(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('greet {name');
    }

    public function testInvalidTokenEmptyBraces(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('greet {}');
    }

    public function testInvalidTokenOptionPrefixSingleDash(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('greet {-f}');
    }

    public function testInvalidOptionWithoutName(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('greet {--=value}');
    }

    public function testInvalidCommandName(): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->parser->parse('--greet {name}');
    }
}
