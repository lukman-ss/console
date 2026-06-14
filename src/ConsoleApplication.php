<?php

declare(strict_types=1);

namespace Lukman\Console;

use Lukman\Console\Command\HelpCommand;
use Lukman\Console\Command\ListCommand;
use Lukman\Console\Exception\CommandNotFoundException;
use Throwable;

class ConsoleApplication
{
    private string $name;
    private string $version;
    private CommandRegistry $registry;

    public function __construct(
        string $name = 'Lukman Console',
        string $version = '1.0.0',
        ?CommandRegistry $registry = null
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->registry = $registry ?? new CommandRegistry();

        if (!$this->registry->has('list')) {
            $this->registry->add(new ListCommand($this));
        }

        if (!$this->registry->has('help')) {
            $this->registry->add(new HelpCommand($this));
        }
    }

    /**
     * Get the application name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get the application version.
     */
    public function version(): string
    {
        return $this->version;
    }

    /**
     * Get the command registry.
     */
    public function registry(): CommandRegistry
    {
        return $this->registry;
    }

    /**
     * Get the command registry (legacy getter).
     */
    public function getRegistry(): CommandRegistry
    {
        return $this->registry;
    }

    /**
     * Add a command to the application.
     */
    public function add(CommandInterface $command): self
    {
        $this->registry->add($command);
        return $this;
    }

    /**
     * Register a console command (legacy method).
     */
    public function register(CommandInterface $command): void
    {
        $this->add($command);
    }

    /**
     * Add a callable command dynamically.
     */
    public function command(string $name, callable $handler, string $description = ''): CommandInterface
    {
        $command = new class($name, $handler, $description) extends Command {
            /** @var callable */
            private $handler;

            public function __construct(string $name, callable $handler, string $description)
            {
                $this->name = $name;
                $this->handler = $handler;
                $this->description = $description;
            }

            public function handle(Input $input, Output $output): int
            {
                $result = ($this->handler)($input, $output);
                return is_int($result) ? $result : self::SUCCESS;
            }
        };

        $this->add($command);
        return $command;
    }

    /**
     * Run the console application.
     */
    public function run(?Input $input = null, ?Output $output = null): int
    {
        $input = $input ?? new Input($_SERVER['argv'] ?? []);
        $output = $output ?? new Output();

        $commandName = $input->commandName();

        if (empty($commandName)) {
            return $this->registry->get('list')->run($input, $output);
        }

        try {
            $command = $this->registry->get($commandName);
        } catch (CommandNotFoundException $e) {
            $output->errorLine(sprintf('Command "%s" is not defined.', $commandName));
            return Command::INVALID;
        }

        try {
            return $command->run($input, $output);
        } catch (Throwable $e) {
            $output->errorLine($e->getMessage());
            return Command::FAILURE;
        }
    }
}
