<?php

declare(strict_types=1);

namespace Lukman\Console;

class CommandDefinition
{
    /**
     * @param string $name
     * @param array<string, array<string, mixed>> $arguments
     * @param array<string, array<string, mixed>> $options
     */
    public function __construct(
        private string $name,
        private array $arguments = [],
        private array $options = []
    ) {
    }

    /**
     * Get the command name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get all arguments.
     *
     * @return array<string, array<string, mixed>>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get all options.
     *
     * @return array<string, array<string, mixed>>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Get a specific argument by name.
     *
     * @return array<string, mixed>|null
     */
    public function argument(string $name): ?array
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Get a specific option by name.
     *
     * @return array<string, mixed>|null
     */
    public function option(string $name): ?array
    {
        return $this->options[$name] ?? null;
    }
}
