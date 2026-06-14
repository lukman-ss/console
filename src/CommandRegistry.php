<?php

declare(strict_types=1);

namespace Lukman\Console;

use Lukman\Console\Exception\CommandNotFoundException;
use Lukman\Console\Exception\InvalidCommandException;

class CommandRegistry
{
    /** @var array<string, CommandInterface> */
    private array $commands = [];

    /**
     * Add a command to the registry.
     *
     * @throws InvalidCommandException
     */
    public function add(CommandInterface $command): void
    {
        try {
            $name = $command->name();
        } catch (\Throwable $e) {
            throw new InvalidCommandException('Invalid command name.', 0, $e);
        }

        if (empty($name)) {
            throw new InvalidCommandException('Command name cannot be empty.');
        }

        $this->commands[$name] = $command;
    }

    /**
     * Add multiple commands to the registry.
     *
     * @param array<int|string, mixed> $commands
     * @throws InvalidCommandException
     */
    public function addMany(array $commands): void
    {
        foreach ($commands as $command) {
            if (!$command instanceof CommandInterface) {
                throw new InvalidCommandException('All items in addMany must implement CommandInterface.');
            }
            $this->add($command);
        }
    }

    /**
     * Get a command by name.
     *
     * @throws CommandNotFoundException
     */
    public function get(string $name): CommandInterface
    {
        if (!$this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" not found.', $name));
        }

        return $this->commands[$name];
    }

    /**
     * Check if a command exists by name.
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * Get all registered commands.
     *
     * @return array<string, CommandInterface>
     */
    public function all(): array
    {
        return $this->commands;
    }

    /**
     * Get all registered command names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->commands);
    }

    /**
     * Remove a command by name.
     */
    public function remove(string $name): void
    {
        unset($this->commands[$name]);
    }

    /**
     * Legacy register method fallback.
     *
     * @throws InvalidCommandException
     */
    public function register(CommandInterface $command): void
    {
        $this->add($command);
    }
}
