<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\Command;
use Lukman\Console\Input;
use Lukman\Console\Output;
use Lukman\Console\Exception\InvalidCommandException;

class CommandTest extends TestCase
{
    public function testCommandName(): void
    {
        $command = new class extends Command {
            protected string $name = 'test:name';
        };

        $this->assertEquals('test:name', $command->name());
    }

    public function testCommandDescription(): void
    {
        $command1 = new class extends Command {
            protected string $name = 'test:name';
        };
        $this->assertEquals('', $command1->description());

        $command2 = new class extends Command {
            protected string $name = 'test:name';
            protected string $description = 'This is a description';
        };
        $this->assertEquals('This is a description', $command2->description());
    }

    public function testCommandSignatureFallback(): void
    {
        $command1 = new class extends Command {
            protected string $name = 'test:name';
        };
        $this->assertEquals('test:name', $command1->signature());

        $command2 = new class extends Command {
            protected string $name = 'test:name';
            protected string $signature = 'test:name {arg}';
        };
        $this->assertEquals('test:name {arg}', $command2->signature());
    }

    public function testRunCallsHandle(): void
    {
        $command = new class extends Command {
            protected string $name = 'test:name';
            public bool $handled = false;

            public function handle(Input $input, Output $output): int
            {
                $this->handled = true;
                return self::FAILURE;
            }
        };

        $input = new Input();
        $output = new Output('php://memory', 'php://memory');
        $result = $command->run($input, $output);

        $this->assertTrue($command->handled);
        $this->assertEquals(Command::FAILURE, $result);
    }

    public function testSuccessConstant(): void
    {
        $this->assertEquals(0, Command::SUCCESS);
    }

    public function testFailureConstant(): void
    {
        $this->assertEquals(1, Command::FAILURE);
    }

    public function testInvalidConstant(): void
    {
        $this->assertEquals(2, Command::INVALID);
    }

    public function testInvalidCommandWithoutNameThrowsException(): void
    {
        $command = new class extends Command {};

        $this->expectException(InvalidCommandException::class);
        $command->name();
    }

    public function testDefaultHandleReturnsSuccess(): void
    {
        $command = new class extends Command {
            protected string $name = 'test:name';
        };

        $result = $command->handle(new Input(), new Output('php://memory', 'php://memory'));

        $this->assertSame(Command::SUCCESS, $result);
    }
}
