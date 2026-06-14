<?php

declare(strict_types=1);

namespace Lukman\Console;

use Lukman\Console\Exception\InvalidCommandException;

class SignatureParser
{
    /**
     * Parses the signature string of a command.
     *
     * @throws InvalidCommandException
     */
    public function parse(string $signature): CommandDefinition
    {
        $signature = trim($signature);
        if ($signature === '') {
            throw new InvalidCommandException('Signature cannot be empty.');
        }

        $tokens = preg_split('/\s+/', $signature);
        if ($tokens === false || empty($tokens)) {
            throw new InvalidCommandException('Invalid signature format.');
        }

        $commandName = $tokens[0];
        if (!$this->isValidName($commandName) || str_starts_with($commandName, '-')) {
            throw new InvalidCommandException(sprintf('Invalid command name "%s" in signature.', $commandName));
        }

        $arguments = [];
        $options = [];

        $tokenCount = count($tokens);
        for ($i = 1; $i < $tokenCount; $i++) {
            $token = $tokens[$i];

            if (!str_starts_with($token, '{') || !str_ends_with($token, '}')) {
                throw new InvalidCommandException(sprintf('Invalid token "%s" in signature. It must be wrapped in braces.', $token));
            }

            $inner = substr($token, 1, -1);
            if ($inner === '') {
                throw new InvalidCommandException('Braces cannot be empty.');
            }

            if (str_starts_with($inner, '--')) {
                // Parse Option
                $opt = substr($inner, 2);
                if ($opt === '' || str_starts_with($opt, '=')) {
                    throw new InvalidCommandException(sprintf('Invalid option token "%s" in signature.', $token));
                }

                if (($pos = strpos($opt, '=')) !== false) {
                    $name = substr($opt, 0, $pos);
                    $defaultVal = substr($opt, $pos + 1);
                    
                    if ($defaultVal === '') {
                        $options[$name] = [
                            'name' => $name,
                            'value_required' => true,
                            'default' => null,
                        ];
                    } else {
                        $options[$name] = [
                            'name' => $name,
                            'value_required' => true,
                            'default' => $defaultVal,
                        ];
                    }
                } else {
                    $name = $opt;
                    $options[$name] = [
                        'name' => $name,
                        'value_required' => false,
                        'default' => false,
                    ];
                }

                if (!$this->isValidName($name)) {
                    throw new InvalidCommandException(sprintf('Invalid option name "%s" in signature.', $name));
                }
            } else {
                // Parse Argument
                if (str_starts_with($inner, '-')) {
                    throw new InvalidCommandException(sprintf('Invalid argument token "%s" in signature.', $token));
                }

                if (str_ends_with($inner, '?')) {
                    $name = substr($inner, 0, -1);
                    $required = false;
                    $default = null;
                } elseif (($pos = strpos($inner, '=')) !== false) {
                    $name = substr($inner, 0, $pos);
                    $required = false;
                    $default = substr($inner, $pos + 1);
                } else {
                    $name = $inner;
                    $required = true;
                    $default = null;
                }

                if (!$this->isValidName($name)) {
                    throw new InvalidCommandException(sprintf('Invalid argument name "%s" in signature.', $name));
                }

                $arguments[$name] = [
                    'name' => $name,
                    'required' => $required,
                    'default' => $default,
                ];
            }
        }

        return new CommandDefinition($commandName, $arguments, $options);
    }

    private function isValidName(string $name): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_\-:]*$/', $name) === 1;
    }
}
