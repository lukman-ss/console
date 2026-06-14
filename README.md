# Lukman Console

A lightweight standalone PHP console package.

## Requirements

- PHP 8.2 or higher

## Installation

```bash
composer require lukman-ss/console
```

## Quick Start

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Lukman\Console\Command;
use Lukman\Console\ConsoleApplication;
use Lukman\Console\Input;
use Lukman\Console\Output;

$app = new ConsoleApplication('My CLI', '1.0.0');

$app->command('greet', function (Input $input, Output $output): int {
    $name = $input->argument(0, 'Guest');
    $output->success('Hello ' . $name);

    return Command::SUCCESS;
}, 'Greet a user');

$exitCode = $app->run();
```

## Commands

Create a command by extending `Lukman\Console\Command`:

```php
<?php

declare(strict_types=1);

use Lukman\Console\Command;
use Lukman\Console\Input;
use Lukman\Console\Output;

final class GreetCommand extends Command
{
    protected string $name = 'greet';
    protected string $description = 'Greet a user';
    protected string $signature = 'greet {name=Guest} {--yell}';

    public function handle(Input $input, Output $output): int
    {
        $message = 'Hello ' . $input->argument(0, 'Guest');

        if ($input->option('yell', false) === true) {
            $message = strtoupper($message);
        }

        $output->writeln($message);

        return self::SUCCESS;
    }
}
```

Register it:

```php
$app->add(new GreetCommand());
```

## Input

`Input` parses argv into:

- script name from `argv[0]`
- command name
- positional arguments
- long options like `--name=value` and `--name value`
- flags like `--force`
- short flags like `-v` and verbosity counts like `-vvv`
- `--no-interaction`

## Output

`Output` writes to stdout and stderr. Memory streams can be passed for testing.

```php
$stdout = fopen('php://memory', 'r+');
$stderr = fopen('php://memory', 'r+');

$output = new Output($stdout, $stderr, decorated: false);
$output->writeln('ok');
$output->errorLine('error');
```

## Built-in Commands

`ConsoleApplication` registers `list` and `help` automatically unless user commands with those names already exist.

```bash
php bin/console list
php bin/console help greet
```

Running without a command uses `list`.

## Testing Commands

`CommandTester` is framework-free and captures stdout, stderr, and exit code.

```php
use Lukman\Console\Testing\CommandTester;

$tester = new CommandTester($app);
$exitCode = $tester->run(['greet', 'Lukman']);

$stdout = $tester->output();
$stderr = $tester->errorOutput();
```

## Tests

```bash
composer test
```
