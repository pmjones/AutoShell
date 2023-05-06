<?php
declare(strict_types=1);

namespace AutoShell;

use Stringable;
use Throwable;

class Shell
{
    public static function new(
        string $namespace,
        string $directory,
        string $method = '__invoke',
        string $suffix = '',
        string|Stringable $header = '',
    ) : Shell
    {
        return new Shell(
            new Config(
                namespace: $namespace,
                directory: $directory,
                method: $method,
                suffix: $suffix,
                header: $header,
            )
        );
    }

    public function __construct(
        public readonly Config $config,
        protected Reflector $reflector = new Reflector()
    ) {
    }

    /**
     * @param array<int, string> $argv
     */
    public function __invoke(array $argv) : Exec
    {
        return $this->newHelp($argv) ?? $this->newExec($argv);
    }

    /**
     * @param array<int, string> $argv
     */
    protected function newHelp(array $argv) : ?Exec
    {
        if (! empty($argv) && strtolower($argv[0]) !== 'help') {
            return null;
        }

        array_shift($argv); // remove 'help', if present

        if (empty($argv)) {
            return new Exec(
                class: Help\RosterCommand::class,
                method: '__invoke',
            );
        }

        $commandName = (string) array_shift($argv);

        return new Exec(
            class: Help\ManualCommand::class,
            method: '__invoke',
            arguments: [
                $commandName,
                $this->getClass($commandName),
                $this->config->method,
            ]
        );
    }

    /**
     * @param array<int, string> $argv
     */
    protected function newExec(array $argv): Exec
    {
        $class = null;
        $arguments = [];
        $error = null;
        $exception = null;

        try {
            $class = $this->getClass((string) array_shift($argv));
            $signature = $this->reflector->getSignature($class, $this->config->method);
            $arguments = $signature->parse($argv);
        } catch (Throwable $e) {
            $error = get_class($e);
            $exception = $e;
        }

        return new Exec(
            class: $class,
            method: $this->config->method,
            arguments: $arguments,
            error: $error,
            exception: $exception,
        );
    }

    /**
     * @return class-string
     */
    public function getClass(string $commandName) : string
    {
        $namespace = rtrim($this->config->namespace, '\\');

        $class = "{$namespace}\\" . str_replace(
            ':',
            '\\',
            str_replace(
                '-',
                '',
                ucfirst(ucwords($commandName, '-:'))
            )
        );

        $class .= $this->config->suffix;

        if (! $this->reflector->isCommandClass($class)) {
            throw new Exception\ClassNotFound($commandName, $class);
        }

        /** @var class-string */
        return $class;
    }
}
