<?php
declare(strict_types=1);

namespace AutoShell;

use Stringable;
use Throwable;

class Console
{
    /**
     * @param callable $factory
     * @param callable $stdout
     * @param callable $stderr
     */
    public static function new(
        string $namespace,
        string $directory,
        string $method = '__invoke',
        string $suffix = '',
        mixed $factory = null,
        mixed $stdout = null,
        mixed $stderr = null,
        string|Stringable $help = '',
    ) : Console
    {
        $shell = Shell::new(
            namespace: $namespace,
            directory: $directory,
            method: $method,
            suffix: $suffix,
            help: $help,
        );

        return new Console(
            $shell,
            $factory ??= fn (string $class) => new $class(),
            $stdout ??= fn (string $output) => fwrite(STDOUT, $output),
            $stderr ??= fn (string $output) => fwrite(STDERR, $output),
        );
    }

    /**
     * @param callable $factory
     * @param callable $stdout
     * @param callable $stderr
     */
    public function __construct(
        protected Shell $shell,
        protected mixed $factory,
        protected mixed $stdout,
        protected mixed $stderr,
    ) {
    }

    /**
     * @param array<int, string> $argv
     */
    public function __invoke(array $argv) : int
    {
        // remove the invoking script
        array_shift($argv);

        // parse the command-line arguments to an Exec descriptor
        $exec = ($this->shell)($argv);

        // errors?
        if ($exec->error === null) {
            $command = $this->newCommand((string) $exec->class);
            $method = $exec->method;
            $arguments = $exec->arguments;
            return $command->$method(...$arguments);
        }

        /** @var Throwable $exception */
        $exception = $exec->exception;
        ($this->stderr)($exception->getMessage() . PHP_EOL);
        $code = (int) $exception->getCode();

        if (! $code) {
            $code = 1;
        }

        return $code;
    }

    protected function newCommand(string $class) : object
    {
        if (is_subclass_of($class, Help\HelpCommand::class)) {
            return new $class(
                config: $this->shell->config,
                format: new Format(),
                stdout: $this->stdout
            );
        }

        /** @var object */
        return ($this->factory)($class);
    }
}
