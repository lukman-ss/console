<?php

declare(strict_types=1);

namespace Lukman\Console;

class Input
{
    /** @var array<int, string> */
    private array $raw = [];
    private ?string $script = null;
    private ?string $commandName = null;
    /** @var array<int|string, mixed> */
    private array $arguments = [];
    /** @var array<string, mixed> */
    private array $options = [];
    private bool $interactive = true;

    /**
     * @param array<int, string> $argv
     * @param array<int|string, mixed> $defaultArguments
     * @param array<string, mixed> $defaultOptions
     */
    public function __construct(array $argv = [], array $defaultArguments = [], array $defaultOptions = [])
    {
        $this->raw = $argv;
        $this->arguments = $defaultArguments;
        $this->options = $defaultOptions;

        if (empty($argv)) {
            return;
        }

        $this->script = $argv[0];
        $tokens = array_slice($argv, 1);

        $tokenCount = count($tokens);
        for ($i = 0; $i < $tokenCount; $i++) {
            $token = $tokens[$i];

            if (str_starts_with($token, '--')) {
                // Handle long options
                $opt = substr($token, 2);
                if (($pos = strpos($opt, '=')) !== false) {
                    $name = substr($opt, 0, $pos);
                    $val = substr($opt, $pos + 1);
                    $this->options[$name] = $val;
                    if ($name === 'no-interaction') {
                        $this->interactive = false;
                    }
                } else {
                    $name = $opt;
                    if ($name === 'no-interaction') {
                        $this->interactive = false;
                    }
                    if ($this->isLongFlag($name)) {
                        $this->options[$name] = true;
                    } elseif ($i + 1 < $tokenCount && !str_starts_with($tokens[$i + 1], '-')) {
                        $this->options[$name] = $tokens[$i + 1];
                        $i++;
                    } else {
                        $this->options[$name] = true;
                    }
                }
            } elseif (str_starts_with($token, '-') && strlen($token) > 1) {
                // Handle short options
                $chars = substr($token, 1);
                if (preg_match('/^v+$/', $chars)) {
                    $this->options['v'] = strlen($chars) === 1 ? true : strlen($chars);
                } else {
                    for ($j = 0; $j < strlen($chars); $j++) {
                        $char = $chars[$j];
                        $this->options[$char] = true;
                    }
                }
            } else {
                if ($this->commandName === null) {
                    $this->commandName = $token;
                    continue;
                }

                // Positional arguments
                $this->arguments[] = $token;
            }
        }
    }

    /**
     * Get the raw argv array.
     *
     * @return array<int, string>
     */
    public function raw(): array
    {
        return $this->raw;
    }

    /**
     * Get the script name.
     */
    public function script(): ?string
    {
        return $this->script;
    }

    /**
     * Get the command name.
     */
    public function commandName(): ?string
    {
        return $this->commandName;
    }

    /**
     * Get all arguments.
     *
     * @return array<int|string, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get a specific argument by index or name.
     */
    public function argument(int|string $key, mixed $default = null): mixed
    {
        return $this->arguments[$key] ?? $default;
    }

    /**
     * Get all options.
     *
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Get a specific option by name.
     */
    public function option(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Check if a specific option exists.
     */
    public function hasOption(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Check if interaction is enabled.
     */
    public function interactive(): bool
    {
        return $this->interactive;
    }

    private function isLongFlag(string $name): bool
    {
        return in_array($name, ['force', 'help', 'verbose', 'no-interaction'], true);
    }
}
