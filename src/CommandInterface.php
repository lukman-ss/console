<?php

declare(strict_types=1);

namespace Lukman\Console;

interface CommandInterface
{
    /**
     * Get the command name.
     */
    public function name(): string;

    /**
     * Get the command description.
     */
    public function description(): string;

    /**
     * Get the command signature.
     */
    public function signature(): string;

    /**
     * Run the command.
     */
    public function run(Input $input, Output $output): int;

    /**
     * Handle/execute the command.
     */
    public function handle(Input $input, Output $output): int;
}
