<?php

declare(strict_types=1);

namespace Lukman\Console\Test;

use PHPUnit\Framework\TestCase;
use Lukman\Console\ConsoleApplication;
use Lukman\Console\Command;
use Lukman\Console\Input;
use Lukman\Console\Output;
use Lukman\Console\Testing\CommandTester;

class CommandTesterTest extends TestCase
{
    private ConsoleApplication $app;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->app = new ConsoleApplication();
        $this->tester = new CommandTester($this->app);
    }

    public function testTesterRunCommand(): void
    {
        $this->app->command('greet', function (Input $input, Output $output) {
            $output->write('Hello World');
            return Command::SUCCESS;
        });

        $exitCode = $this->tester->run(['greet']);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function testTesterCapturesStdout(): void
    {
        $this->app->command('greet', function (Input $input, Output $output) {
            $output->write('stdout content');
            return Command::SUCCESS;
        });

        $this->tester->run(['greet']);
        $this->assertEquals('stdout content', $this->tester->output());
    }

    public function testTesterCapturesStderr(): void
    {
        $this->app->command('greet', function (Input $input, Output $output) {
            $output->error('stderr content');
            return Command::SUCCESS;
        });

        $this->tester->run(['greet']);
        $this->assertEquals('stderr content', $this->tester->errorOutput());
        $this->assertEquals('', $this->tester->output());
    }

    public function testTesterCapturesExitCode(): void
    {
        $this->app->command('greet', function () {
            return Command::FAILURE;
        });

        $exitCode = $this->tester->run(['greet']);
        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertEquals(Command::FAILURE, $this->tester->exitCode());
    }

    public function testClearOutput(): void
    {
        $this->app->command('greet', function (Input $input, Output $output) {
            $output->write('hello');
            $output->error('world');
            return Command::SUCCESS;
        });

        $this->tester->run(['greet']);
        $this->assertEquals('hello', $this->tester->output());
        $this->assertEquals('world', $this->tester->errorOutput());

        $this->tester->clear();
        $this->assertEquals('', $this->tester->output());
        $this->assertEquals('', $this->tester->errorOutput());
        $this->assertNull($this->tester->exitCode());
    }

    public function testRunTwiceBufferIsolated(): void
    {
        $this->app->command('greet', function (Input $input, Output $output) {
            $output->write('run content');
            return Command::SUCCESS;
        });

        $this->tester->run(['greet']);
        $this->assertEquals('run content', $this->tester->output());

        $this->tester->run(['greet']);
        $this->assertEquals('run content', $this->tester->output());
    }

    public function testRunCreatesInputFromArgv(): void
    {
        $this->app->command('show', function (Input $input, Output $output) {
            $output->write((string) $input->argument(0));
            $output->error((string) $input->option('name'));
            return Command::SUCCESS;
        });

        $exitCode = $this->tester->run(['show', 'value', '--name=stderr']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertSame('value', $this->tester->output());
        $this->assertSame('stderr', $this->tester->errorOutput());
    }
}
