<?php

declare(strict_types=1);

namespace Lukman\Console\Testing;

use Lukman\Console\ConsoleApplication;
use Lukman\Console\Input;
use Lukman\Console\Output;

class CommandTester
{
    /** @var resource|null */
    private $stdout = null;

    /** @var resource|null */
    private $stderr = null;

    private ?int $exitCode = null;

    public function __construct(private ConsoleApplication $app)
    {
    }

    public function __destruct()
    {
        $this->clear();
    }

    /**
     * Run the application with the given arguments.
     */
    public function run(array $argv): int
    {
        $this->clear();

        $this->stdout = fopen('php://memory', 'r+');
        $this->stderr = fopen('php://memory', 'r+');
        $output = new Output($this->stdout, $this->stderr);

        $inputArgv = array_merge(['app'], $argv);
        $input = new Input($inputArgv);

        $this->exitCode = $this->app->run($input, $output);

        return $this->exitCode;
    }

    /**
     * Get the last exit code.
     */
    public function exitCode(): ?int
    {
        return $this->exitCode;
    }

    /**
     * Get the stdout output contents.
     */
    public function output(): string
    {
        if ($this->stdout === null) {
            return '';
        }
        rewind($this->stdout);
        return stream_get_contents($this->stdout);
    }

    /**
     * Get the stderr output contents.
     */
    public function errorOutput(): string
    {
        if ($this->stderr === null) {
            return '';
        }
        rewind($this->stderr);
        return stream_get_contents($this->stderr);
    }

    /**
     * Reset buffers and close handles.
     */
    public function clear(): void
    {
        if ($this->stdout !== null) {
            fclose($this->stdout);
            $this->stdout = null;
        }
        if ($this->stderr !== null) {
            fclose($this->stderr);
            $this->stderr = null;
        }

        $this->exitCode = null;
    }
}
