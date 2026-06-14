<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\ConsoleApplication;
use Lukman\Console\Command;
use Lukman\Console\CommandRegistry;
use Lukman\Console\Input;
use Lukman\Console\Output;
use Lukman\Console\Exception\CommandNotFoundException;

class HelpListTest extends TestCase
{
    private function createMockCommand(string $name, string $description = '', string $signature = ''): Command
    {
        return new class($name, $description, $signature) extends Command {
            public function __construct(string $name, string $description, string $signature)
            {
                $this->name = $name;
                $this->description = $description;
                $this->signature = $signature;
            }

            public function handle(Input $input, Output $output): int
            {
                return self::SUCCESS;
            }
        };
    }

    public function testListCommandOutput(): void
    {
        $app = new ConsoleApplication('Test App', '2.0.0');
        $app->add($this->createMockCommand('foo', 'Foo command'));

        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $app->run(new Input(['bin/console', 'list']), $output);

        rewind($stdout);
        $content = stream_get_contents($stdout);
        $this->assertStringContainsString('Test App (2.0.0)', $content);
        $this->assertStringContainsString('foo', $content);
        $this->assertStringContainsString('Foo command', $content);
    }

    public function testListCommandSortedAscending(): void
    {
        $app = new ConsoleApplication();
        $app->add($this->createMockCommand('zebra', 'Zebra'));
        $app->add($this->createMockCommand('apple', 'Apple'));

        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $app->run(new Input(['bin/console', 'list']), $output);

        rewind($stdout);
        $content = stream_get_contents($stdout);
        $applePos = strpos($content, 'apple');
        $zebraPos = strpos($content, 'zebra');

        $this->assertNotFalse($applePos);
        $this->assertNotFalse($zebraPos);
        $this->assertTrue($applePos < $zebraPos);
    }

    public function testHelpWithoutArgumentFallbacksToList(): void
    {
        $app = new ConsoleApplication();

        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $exitCode = $app->run(new Input(['bin/console', 'help']), $output);

        rewind($stdout);
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Available commands:', stream_get_contents($stdout));
    }

    public function testHelpCommandOutput(): void
    {
        $app = new ConsoleApplication();
        $app->add($this->createMockCommand('greet', 'Greet user', 'greet {name=Guest} {--yell} {--path=app}'));

        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $app->run(new Input(['bin/console', 'help', 'greet']), $output);

        rewind($stdout);
        $content = stream_get_contents($stdout);
        $this->assertStringContainsString('Name: greet', $content);
        $this->assertStringContainsString('Description: Greet user', $content);
        $this->assertStringContainsString('Signature: greet {name=Guest} {--yell} {--path=app}', $content);
        $this->assertStringContainsString('Arguments:', $content);
        $this->assertStringContainsString('name (optional) [default: Guest]', $content);
        $this->assertStringContainsString('Options:', $content);
        $this->assertStringContainsString('--yell', $content);
        $this->assertStringContainsString('--path (value required) [default: app]', $content);
    }

    public function testHelpUnknownCommandThrowsCommandNotFoundException(): void
    {
        $app = new ConsoleApplication();

        $this->expectException(CommandNotFoundException::class);
        $app->registry()->get('help')->run(new Input(['bin/console', 'help', 'missing']), new Output('php://memory', 'php://memory'));
    }

    public function testRunWithoutCommandUsesList(): void
    {
        $app = new ConsoleApplication('Default List App', '3.1');
        
        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $exitCode = $app->run(new Input(['bin/console']), $output);

        rewind($stdout);
        $content = stream_get_contents($stdout);
        $this->assertStringContainsString('Default List App (3.1)', $content);
        $this->assertEquals(0, $exitCode);
    }

    public function testUserCommandWithSameNameNotOverridden(): void
    {
        $registry = new CommandRegistry();
        $customList = new class extends Command {
            protected string $name = 'list';
            protected string $description = 'My Custom List';
            public function handle(Input $input, Output $output): int
            {
                $output->write('custom list output');
                return self::SUCCESS;
            }
        };
        $registry->add($customList);

        $app = new ConsoleApplication('Custom App', '1.0.0', $registry);

        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $app->run(new Input(['bin/console', 'list']), $output);

        rewind($stdout);
        $content = stream_get_contents($stdout);
        $this->assertEquals('custom list output', $content);
    }

    public function testUserHelpCommandNotOverridden(): void
    {
        $registry = new CommandRegistry();
        $customHelp = new class extends Command {
            protected string $name = 'help';
            protected string $description = 'My Custom Help';
            public function handle(Input $input, Output $output): int
            {
                $output->write('custom help output');
                return self::SUCCESS;
            }
        };
        $registry->add($customHelp);

        $app = new ConsoleApplication('Custom App', '1.0.0', $registry);

        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $app->run(new Input(['bin/console', 'help']), $output);

        rewind($stdout);
        $this->assertEquals('custom help output', stream_get_contents($stdout));
    }
}
