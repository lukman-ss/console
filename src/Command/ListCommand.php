<?php

declare(strict_types=1);

namespace Lukman\Console\Command;

use Lukman\Console\Command;
use Lukman\Console\Input;
use Lukman\Console\Output;
use Lukman\Console\ConsoleApplication;

class ListCommand extends Command
{
    protected string $name = 'list';
    protected string $description = 'List all available commands';

    public function __construct(private ConsoleApplication $app)
    {
    }

    public function handle(Input $input, Output $output): int
    {
        $output->writeln($this->app->name() . ' (' . $this->app->version() . ')');
        $output->writeln('');
        $output->writeln('Available commands:');

        $commands = $this->app->registry()->all();
        ksort($commands);

        foreach ($commands as $cmd) {
            $desc = $cmd->description();
            $output->writeln('  ' . $cmd->name() . ($desc !== '' ? '   ' . $desc : ''));
        }

        return self::SUCCESS;
    }
}
