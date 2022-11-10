<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

class Shell
{
    public static function new(
        string $namespace,
        string $directory,
        string $method = '__invoke',
        string $suffix = '',
    ) : Shell
    {
        $filter = new Filter();
        $getopt = new Getopt($filter);

        return new Shell(
            new Config(
                namespace: $namespace,
                directory: $directory,
                method: $method,
                suffix: $suffix,
            ),
            $getopt,
            $filter,
        );
    }

    public function __construct(
        public readonly Config $config,
        protected Getopt $getopt,
        protected Filter $filter,
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
            class: Help\ManualCommand::CLASS,
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
        $options = null;
        $arguments = [];
        $exception = null;

        try {
            $class = $this->getClass((string) array_shift($argv));
            $rc = $this->reflector->getClass($class);
            $options = $this->getOptions($rc, $argv);
            $rm = $this->reflector->getMethod($rc, $this->config->method);
            $arguments = $this->getArguments($rm, $argv);
        } catch (Throwable $e) {
            $exception = $e;
        }

        return new Exec(
            class: $class,
            method: $this->config->method,
            options: $options,
            arguments: $arguments,
            error: $exception,
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

        if (! class_exists($class)) {
            throw new Exception\ClassNotFound($commandName, $class);
        }

        return $class;
    }

    /**
     * @param array<int, string> &$argv
     */
    protected function getOptions(
        ReflectionClass $rc,
        array &$argv
    ) : Options
    {
        $optionCollection = $this->newOptionCollection($rc);
        $argv = $this->getopt->parse($optionCollection, $argv);
        return $this->newOptions($optionCollection);
    }

    /**
     * @param array<int, string> &$argv
     * @return array<int, mixed>
     */
    protected function getArguments(
        ReflectionMethod $rm,
        array &$argv
    ) : array
    {
        $arguments = [];
        $argumentParameters = $this->reflector->getArgumentParameters($rm);

        foreach ($argumentParameters as $argumentParameter) {
            $this->addArgument($argumentParameter, $argv, $arguments);
        }

        return $arguments;
    }

    /**
     * @param array<int, string> &$argv
     * @param array<int, mixed> &$arguments
     */
    protected function addArgument(
        ReflectionParameter $parameter,
        array &$argv,
        array &$arguments
    ) : void
    {
        $pos = count($arguments);
        $name = $parameter->getName();
        $type = $this->reflector->getParameterType($parameter);

        if (empty($argv) && ! $parameter->isOptional()) {
            throw new Exception\ArgumentRequired("Argument {$pos} (\${$name}) is missing.");
        }

        $errmsg = "Argument {$pos} (\${$name}) expected {$type} value";

        if (! $parameter->isVariadic()) {
            $value = array_shift($argv);
            $arguments[] = ($this->filter)($value, $type, $errmsg);
            return;
        }

        // variadic; capture all remaining argv
        while (! empty($argv)) {
            $value = array_shift($argv);
            $arguments[] = ($this->filter)($value, $type, $errmsg);
        }
    }

    protected function newOptionCollection(
        ReflectionClass $rc
    ) : OptionCollection
    {
        return new OptionCollection(
            $this->reflector->getOptionAttributes($rc)
        );
    }

    protected function newOptions(OptionCollection $optionCollection) : Options
    {
        return new Options($optionCollection);
    }
}
