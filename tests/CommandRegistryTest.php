<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\Command;
use Lukman\Console\CommandRegistry;
use Lukman\Console\Exception\CommandNotFoundException;
use Lukman\Console\Exception\InvalidCommandException;

class CommandRegistryTest extends TestCase
{
    private CommandRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new CommandRegistry();
    }

    private function createMockCommand(string $name): Command
    {
        return new class($name) extends Command {
            public function __construct(string $name)
            {
                $this->name = $name;
            }
        };
    }

    public function testAddCommand(): void
    {
        $command = $this->createMockCommand('test:command');
        $this->registry->add($command);

        $this->assertSame($command, $this->registry->get('test:command'));
    }

    public function testGetCommand(): void
    {
        $command = $this->createMockCommand('test:command');
        $this->registry->add($command);

        $this->assertSame($command, $this->registry->get('test:command'));
    }

    public function testHasTrue(): void
    {
        $command = $this->createMockCommand('test:command');
        $this->registry->add($command);

        $this->assertTrue($this->registry->has('test:command'));
    }

    public function testHasFalse(): void
    {
        $this->assertFalse($this->registry->has('test:command'));
    }

    public function testDuplicateReplacesOld(): void
    {
        $command1 = $this->createMockCommand('test:command');
        $command2 = $this->createMockCommand('test:command');

        $this->registry->add($command1);
        $this->registry->add($command2);

        $this->assertSame($command2, $this->registry->get('test:command'));
        $this->assertCount(1, $this->registry->all());
    }

    public function testAddMany(): void
    {
        $command1 = $this->createMockCommand('cmd1');
        $command2 = $this->createMockCommand('cmd2');

        $this->registry->addMany([$command1, $command2]);

        $this->assertTrue($this->registry->has('cmd1'));
        $this->assertTrue($this->registry->has('cmd2'));
        $this->assertCount(2, $this->registry->all());
    }

    public function testAddManyInvalidItemThrow(): void
    {
        $command1 = $this->createMockCommand('cmd1');
        $invalidItem = new \stdClass();

        $this->expectException(InvalidCommandException::class);
        $this->registry->addMany([$command1, $invalidItem]);
    }

    public function testMissingCommandThrowsCommandNotFoundException(): void
    {
        $this->expectException(CommandNotFoundException::class);
        $this->registry->get('missing');
    }

    public function testNames(): void
    {
        $command1 = $this->createMockCommand('cmd1');
        $command2 = $this->createMockCommand('cmd2');

        $this->registry->addMany([$command1, $command2]);

        $this->assertEquals(['cmd1', 'cmd2'], $this->registry->names());
    }

    public function testRemove(): void
    {
        $command = $this->createMockCommand('test:command');
        $this->registry->add($command);

        $this->assertTrue($this->registry->has('test:command'));

        $this->registry->remove('test:command');
        $this->assertFalse($this->registry->has('test:command'));

        $this->registry->remove('nonexistent');
        $this->addToAssertionCount(1);
    }
}
