<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use Lukman\Console\Command;
use Lukman\Console\Command\HelpCommand;
use Lukman\Console\Command\ListCommand;
use Lukman\Console\CommandInterface;
use Lukman\Console\CommandDefinition;
use Lukman\Console\CommandRegistry;
use Lukman\Console\Console;
use Lukman\Console\ConsoleApplication;
use Lukman\Console\Input;
use Lukman\Console\Output;
use Lukman\Console\SignatureParser;
use Lukman\Console\Exception\CommandNotFoundException;
use Lukman\Console\Exception\ConsoleException;
use Lukman\Console\Exception\InvalidCommandException;
use Lukman\Console\Testing\CommandTester;
use PHPUnit\Framework\TestCase;

class AutoloadTest extends TestCase
{
    public function testClassesCanBeLoaded(): void
    {
        $this->assertTrue(class_exists(ConsoleApplication::class));
        $this->assertTrue(class_exists(Console::class));
        $this->assertTrue(class_exists(Command::class));
        $this->assertTrue(interface_exists(CommandInterface::class));
        $this->assertTrue(class_exists(CommandRegistry::class));
        $this->assertTrue(class_exists(Input::class));
        $this->assertTrue(class_exists(Output::class));
        $this->assertTrue(class_exists(SignatureParser::class));
        $this->assertTrue(class_exists(CommandDefinition::class));
        $this->assertTrue(class_exists(ConsoleException::class));
        $this->assertTrue(class_exists(CommandNotFoundException::class));
        $this->assertTrue(class_exists(InvalidCommandException::class));
        $this->assertTrue(class_exists(ListCommand::class));
        $this->assertTrue(class_exists(HelpCommand::class));
        $this->assertTrue(class_exists(CommandTester::class));
    }
}
