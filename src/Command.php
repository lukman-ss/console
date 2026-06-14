<?php

declare(strict_types=1);

namespace Lukman\Console;

use Lukman\Console\Exception\InvalidCommandException;

abstract class Command implements CommandInterface
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;

    protected string $name = '';
    protected string $description = '';
    protected string $signature = '';

    /**
     * Get the command name.
     *
     * @throws InvalidCommandException
     */
    public function name(): string
    {
        if (empty($this->name)) {
            throw new InvalidCommandException('Command name cannot be empty.');
        }

        return $this->name;
    }

    /**
     * Get the command description.
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * Get the command signature.
     *
     * @throws InvalidCommandException
     */
    public function signature(): string
    {
        if (empty($this->signature)) {
            return $this->name();
        }

        return $this->signature;
    }

    /**
     * Run the command.
     */
    public function run(Input $input, Output $output): int
    {
        return $this->handle($input, $output);
    }

    /**
     * Handle the command logic.
     */
    public function handle(Input $input, Output $output): int
    {
        return self::SUCCESS;
    }
}
