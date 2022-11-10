<?php
declare(strict_types=1);

namespace AutoShell;

class Console
{
    /**
     * @param callable $factory
     * @param resource $stdout
     * @param resource $stderr
     */
    public static function new(
        string $namespace,
        string $directory,
        string $method = '__invoke',
        string $suffix = '',
        mixed $factory = null,
        mixed $stdout = STDOUT,
        mixed $stderr = STDERR,
    ) : Console
    {
        $shell = Shell::new(
            namespace: $namespace,
            directory: $directory,
            method: $method,
            suffix: $suffix,
        );

        $factory ??= function (string $class) : object {
            return new $class();
        };

        return new Console(
            $shell,
            $factory,
            $stdout,
            $stderr
        );
    }

    /**
     * @param callable $factory
     * @param resource $stdout
     * @param resource $stderr
     */
    public function __construct(
        protected Shell $shell,
        protected mixed $factory,
        protected mixed $stdout = STDOUT,
        protected mixed $stderr = STDERR,
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
            $options = $exec->options;
            $arguments = $exec->arguments;
            return $command->$method($options, ...$arguments);
        }

        fwrite($this->stderr, $exec->exception->getMessage() . PHP_EOL);
        $code = (int) $exec->exception->getCode();

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
                stdout: $this->stdout
            );
        }

        /** @var object */
        return ($this->factory)($class);
    }
}
