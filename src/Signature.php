<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionParameter;
use ReflectionClass;

class Signature
{
    /**
     * @var array<int, string>
     */
    protected array $argv = [];

    /**
     * @var array<int, ReflectionParameter>
     */
    protected array $argumentParameters = [];

    /**
     * @var array<int, Option>
     */
    protected array $optionCollection = [];

    /**
     * @var array<string, array<string, Option>>
     */
    protected array $optionCollectionByClass = [];

    /**
     * @param class-string $commandClass
     * @param array <int, ReflectionParameter> $methodParameters
     */
    public function __construct(
        public readonly string $commandClass,
        public readonly string $commandMethod,
        public readonly array $methodParameters,
        protected Reflector $reflector,
        protected Filter $filter,
    ) {
        foreach ($this->methodParameters as $methodParameter) {
            if ($this->reflector->isOptionsParameter($methodParameter)) {
                $this->addOptionCollection($methodParameter);
            } else {
                $this->argumentParameters[] = $methodParameter;
            }
        }

        usort(
            $this->optionCollection,
            fn (Option $a, Option $b) => $a->names <=> $b->names,
        );
    }

    protected function addOptionCollection(ReflectionParameter $optionsParameter) : void
    {
        /** @var class-string */
        $optionsClass = $this->reflector->getParameterType($optionsParameter);
        $optionCollection = $this->reflector->getOptionCollection($optionsClass);
        $this->optionCollectionByClass[$optionsClass] = $optionCollection;
        $this->optionCollection = array_merge(
            $this->optionCollection,
            array_values($optionCollection),
        );
    }

    /**
     * @param array<int, string> $argv
     * @return mixed[]
     */
    public function parse(array $argv) : array
    {
        $optionParser = new OptionParser(
            $this->optionCollection,
            $this->reflector,
            $this->filter,
        );
        $this->argv = $optionParser($argv);
        $arguments = [];

        foreach ($this->methodParameters as $methodParameter) {
            $this->reflector->isOptionsParameter($methodParameter)
                ? $this->appendOptions($methodParameter, $arguments)
                : $this->appendArgument($methodParameter, $arguments);
        }

        return $arguments;
    }

    /**
     * @param array<int, mixed> &$arguments
     */
    protected function appendOptions(
        ReflectionParameter $optionsParameter,
        array &$arguments,
    ) : void
    {
        $optionsClass = $this->reflector->getParameterType($optionsParameter);
        $optionCollection = $this->optionCollectionByClass[$optionsClass];
        $values = [];

        foreach ($optionCollection as $name => $option) {
            $values[$name] = $option->getValue();
        }

        /** @var Options */
        $options = new $optionsClass(...$values);
        $arguments[] = $options;
    }

    /**
     * @param array<int, mixed> &$arguments
     */
    protected function appendArgument(
        ReflectionParameter $argumentParameter,
        array &$arguments,
    ) : void
    {
        $pos = count($arguments);
        $name = $argumentParameter->getName();
        $type = $this->reflector->getParameterType($argumentParameter);

        if (empty($this->argv) && ! $argumentParameter->isOptional()) {
            throw new Exception\ArgumentRequired(
                "Argument {$pos} (\${$name}) is missing.",
            );
        }

        $errmsg = "Argument {$pos} (\${$name}) expected {$type} value";

        if ($argumentParameter->isVariadic()) {
            while (! empty($this->argv)) {
                $value = array_shift($this->argv);
                $arguments[] = ($this->filter)($value, $type, $errmsg);
            }

            return;
        }

        $value = empty($this->argv)
            ? $argumentParameter->getDefaultValue()
            : array_shift($this->argv);
        $arguments[] = ($this->filter)($value, $type, $errmsg);
    }

    /**
     * @return array<int, ReflectionParameter>
     */
    public function getArgumentParameters() : array
    {
        return $this->argumentParameters;
    }

    /**
     * @return array<int, Option>
     */
    public function getOptionCollection() : array
    {
        return $this->optionCollection;
    }

    public function getCommandHelp() : ?Help
    {
        return $this->reflector
            ->getHelp($this->reflector->getClass($this->commandClass));
    }

    public function getArgumentHelp(int $argumentNumber) : ?Help
    {
        return $this->reflector->getHelp($this->argumentParameters[$argumentNumber]);
    }
}
