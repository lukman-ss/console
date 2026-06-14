<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\ConsoleApplication;
use Lukman\Console\Command;
use Lukman\Console\Input;
use Lukman\Console\Output;

class ConsoleApplicationTest extends TestCase
{
    public function testAppName(): void
    {
        $app = new ConsoleApplication('Custom App');
        $this->assertEquals('Custom App', $app->name());
    }

    public function testAppVersion(): void
    {
        $app = new ConsoleApplication('App', '2.3.4');
        $this->assertEquals('2.3.4', $app->version());
    }

    public function testAddCommand(): void
    {
        $app = new ConsoleApplication();
        $command = new class extends Command {
            protected string $name = 'test:cmd';
        };

        $result = $app->add($command);
        $this->assertSame($app, $result);
        $this->assertTrue($app->registry()->has('test:cmd'));
    }

    public function testCommandCallable(): void
    {
        $app = new ConsoleApplication();
        $called = false;
        
        $command = $app->command('test:callable', function (Input $input, Output $output) use (&$called) {
            $called = true;
            return Command::SUCCESS;
        }, 'A callable command');

        $this->assertEquals('test:callable', $command->name());
        $this->assertEquals('A callable command', $command->description());

        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $command->run(new Input(), $output);
        $this->assertTrue($called);
    }

    public function testRunCommandSuccess(): void
    {
        $app = new ConsoleApplication();
        $app->command('greet', function (Input $input, Output $output) {
            $output->write('Hello World');
            return Command::SUCCESS;
        });

        $input = new Input(['bin/console', 'greet']);
        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $exitCode = $app->run($input, $output);

        rewind($stdout);
        $this->assertEquals('Hello World', stream_get_contents($stdout));
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function testRunUnknownCommand(): void
    {
        $app = new ConsoleApplication();

        $input = new Input(['bin/console', 'unknown']);
        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $exitCode = $app->run($input, $output);

        rewind($stderr);
        $this->assertStringContainsString('Command "unknown" is not defined.', stream_get_contents($stderr));
        $this->assertEquals(Command::INVALID, $exitCode);
    }

    public function testRunWithoutCommand(): void
    {
        $app = new ConsoleApplication('My CLI', '1.2.3');
        $app->command('cmd1', function () {}, 'Description 1');

        $input = new Input(['bin/console']);
        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $exitCode = $app->run($input, $output);

        rewind($stdout);
        $out = stream_get_contents($stdout);
        $this->assertStringContainsString('My CLI (1.2.3)', $out);
        $this->assertStringContainsString('Available commands:', $out);
        $this->assertStringContainsString('cmd1', $out);
        $this->assertEquals(0, $exitCode);
    }

    public function testCommandExceptionReturnFailure(): void
    {
        $app = new ConsoleApplication();
        $app->command('fail', function () {
            throw new \RuntimeException('Something went wrong');
        });

        $input = new Input(['bin/console', 'fail']);
        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $exitCode = $app->run($input, $output);

        rewind($stderr);
        $this->assertStringContainsString('Something went wrong', stream_get_contents($stderr));
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function testRunDoesNotExit(): void
    {
        $app = new ConsoleApplication();
        $app->command('exit-check', function () {
            return Command::SUCCESS;
        });

        $input = new Input(['bin/console', 'exit-check']);
        $stdout = fopen('php://memory', 'r+');
        $stderr = fopen('php://memory', 'r+');
        $output = new Output($stdout, $stderr);

        $exitCode = $app->run($input, $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }
}
