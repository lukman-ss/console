<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\Input;

class InputTest extends TestCase
{
    public function testScript(): void
    {
        $input = new Input(['bin/console', 'test']);
        $this->assertEquals('bin/console', $input->script());
    }

    public function testCommandName(): void
    {
        $input = new Input(['bin/console', 'make:model']);
        $this->assertEquals('make:model', $input->commandName());

        $input2 = new Input(['bin/console', '--help']);
        $this->assertNull($input2->commandName());
    }

    public function testPositionalArguments(): void
    {
        $input = new Input(['bin/console', 'greet', 'John', 'Doe']);
        $this->assertEquals(['John', 'Doe'], $input->arguments());
        $this->assertEquals('John', $input->argument(0));
        $this->assertEquals('Doe', $input->argument(1));
    }

    public function testLongOptionWithEquals(): void
    {
        $input = new Input(['bin/console', 'greet', '--name=John']);
        $this->assertEquals('John', $input->option('name'));
        $this->assertTrue($input->hasOption('name'));
    }

    public function testLongOptionWithSeparatedValue(): void
    {
        $input = new Input(['bin/console', 'greet', '--name', 'John']);
        $this->assertEquals('John', $input->option('name'));
        $this->assertTrue($input->hasOption('name'));
    }

    public function testLongFlag(): void
    {
        $input = new Input(['bin/console', 'greet', '--force']);
        $this->assertTrue($input->option('force'));
        $this->assertTrue($input->hasOption('force'));
    }

    public function testNoInteraction(): void
    {
        $input = new Input(['bin/console', 'greet', '--no-interaction']);
        $this->assertFalse($input->interactive());
        $this->assertTrue($input->hasOption('no-interaction'));

        $input2 = new Input(['bin/console', 'greet']);
        $this->assertTrue($input2->interactive());
    }

    public function testShortFlag(): void
    {
        $input = new Input(['bin/console', 'greet', '-f']);
        $this->assertTrue($input->option('f'));
        $this->assertTrue($input->hasOption('f'));
    }

    public function testShortVerbosityCount(): void
    {
        $input = new Input(['bin/console', 'greet', '-vvv']);
        $this->assertEquals(3, $input->option('v'));

        $input2 = new Input(['bin/console', 'greet', '-v']);
        $this->assertTrue($input2->option('v'));

        $input3 = new Input(['bin/console', 'greet', '-vv']);
        $this->assertEquals(2, $input3->option('v'));
    }

    public function testDefaultArgument(): void
    {
        $input = new Input(['bin/console', 'greet']);
        $this->assertNull($input->argument(0));
        $this->assertEquals('default', $input->argument(0, 'default'));
        $this->assertEquals('default', $input->argument('nonexistent', 'default'));

        $input2 = new Input(['bin/console', 'greet'], ['Guest']);
        $this->assertEquals('Guest', $input2->argument(0));
    }

    public function testDefaultOption(): void
    {
        $input = new Input(['bin/console', 'greet']);
        $this->assertNull($input->option('name'));
        $this->assertEquals('default', $input->option('name', 'default'));

        $input2 = new Input(['bin/console', 'greet'], [], ['name' => 'Guest']);
        $this->assertEquals('Guest', $input2->option('name'));
    }

    public function testCommandAfterOption(): void
    {
        $input = new Input(['bin/console', '--verbose', 'greet', 'John']);

        $this->assertEquals('greet', $input->commandName());
        $this->assertTrue($input->option('verbose'));
        $this->assertEquals(['John'], $input->arguments());
    }
}
