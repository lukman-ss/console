<?php

declare(strict_types=1);

namespace Lukman\Console;

class Output
{
    /** @var resource */
    private $stdoutStream;

    /** @var resource */
    private $stderrStream;

    private bool $decorated;

    /**
     * @param resource|string|null $stdout
     * @param resource|string|null $stderr
     */
    public function __construct($stdout = null, $stderr = null, bool $decorated = false)
    {
        $this->decorated = $decorated;
        $this->stdoutStream = $this->resolveStream($stdout, 'php://stdout');
        $this->stderrStream = $this->resolveStream($stderr, 'php://stderr');
    }

    /**
     * @param resource|string|null $stream
     * @return resource
     */
    private function resolveStream($stream, string $fallback)
    {
        if (is_resource($stream)) {
            return $stream;
        }

        if (is_string($stream)) {
            $opened = @fopen($stream, 'w');
            if (is_resource($opened)) {
                return $opened;
            }
        }

        if ($fallback === 'php://stdout' && defined('STDOUT') && is_resource(STDOUT)) {
            return STDOUT;
        }

        if ($fallback === 'php://stderr' && defined('STDERR') && is_resource(STDERR)) {
            return STDERR;
        }

        $opened = @fopen($fallback, 'w');
        if (is_resource($opened)) {
            return $opened;
        }

        return fopen('php://memory', 'w+');
    }

    /**
     * Write a message to stdout.
     */
    public function write(string $message): void
    {
        fwrite($this->stdoutStream, $message);
    }

    /**
     * Write a message to stdout followed by a newline.
     */
    public function writeln(string $message = ''): void
    {
        $this->write($message . PHP_EOL);
    }

    /**
     * Write an error message to stderr without a newline.
     */
    public function error(string $message): void
    {
        fwrite($this->stderrStream, $message);
    }

    /**
     * Write an error message to stderr with a newline.
     */
    public function errorLine(string $message = ''): void
    {
        $this->error($message . PHP_EOL);
    }

    /**
     * Write a success message to stdout with color/decorations if enabled.
     */
    public function success(string $message): void
    {
        $this->writeln($this->format($message, '32'));
    }

    /**
     * Write a warning message to stdout with color/decorations if enabled.
     */
    public function warning(string $message): void
    {
        $this->writeln($this->format($message, '33'));
    }

    /**
     * Write an info message to stdout with color/decorations if enabled.
     */
    public function info(string $message): void
    {
        $this->writeln($this->format($message, '36'));
    }

    /**
     * Check if output is decorated.
     */
    public function decorated(): bool
    {
        return $this->decorated;
    }

    /**
     * Check if output is decorated.
     */
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * Set decorated flag.
     */
    public function setDecorated(bool $decorated): self
    {
        $this->decorated = $decorated;

        return $this;
    }

    private function format(string $message, string $color): string
    {
        if (!$this->decorated) {
            return $message;
        }

        return "\033[" . $color . 'm' . $message . "\033[0m";
    }
}
