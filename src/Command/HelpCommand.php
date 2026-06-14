<?php

declare(strict_types=1);

namespace Lukman\Console\Command;

use Lukman\Console\Command;
use Lukman\Console\Input;
use Lukman\Console\Output;
use Lukman\Console\ConsoleApplication;
use Lukman\Console\SignatureParser;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = 'Display help for a command';
    protected string $signature = 'help {command_name?}';

    public function __construct(private ConsoleApplication $app)
    {
    }

    public function handle(Input $input, Output $output): int
    {
        $cmdName = $input->argument(0);
        if ($cmdName === null) {
            $listCmd = $this->app->registry()->get('list');
            return $listCmd->run($input, $output);
        }

        $targetCmd = $this->app->registry()->get($cmdName);
        $parser = new SignatureParser();
        $definition = $parser->parse($targetCmd->signature());

        $output->writeln('Name: ' . $targetCmd->name());
        $output->writeln('Description: ' . $targetCmd->description());
        $output->writeln('Signature: ' . $targetCmd->signature());

        $arguments = $definition->arguments();
        if (!empty($arguments)) {
            $output->writeln('');
            $output->writeln('Arguments:');
            foreach ($arguments as $arg) {
                $output->writeln('  ' . $arg['name'] . ($arg['required'] ? ' (required)' : ' (optional)') . ($arg['default'] !== null ? ' [default: ' . $arg['default'] . ']' : ''));
            }
        }

        $options = $definition->options();
        if (!empty($options)) {
            $output->writeln('');
            $output->writeln('Options:');
            foreach ($options as $opt) {
                $output->writeln('  --' . $opt['name'] . ($opt['value_required'] ? ' (value required)' : '') . ($opt['default'] !== null ? ' [default: ' . $opt['default'] . ']' : ''));
            }
        }

        return self::SUCCESS;
    }
}
