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
     * @var array<string, Option>
     */
    protected array $optionsByName = [];

    /**
     * @var array<int, Option>
     */
    protected array $optionAttributes = [];

    /**
     * @var array<string, array<string, Option>>
     */
    protected array $optionAttributesByClass = [];

    /**
     * @param class-string $commandClass
     * @param array <int, ReflectionParameter> $methodParameters
     */
    public function __construct(
        public readonly string $commandClass,
        public readonly string $commandMethod,
        public readonly array $methodParameters,
        protected Reflector $reflector = new Reflector(),
        protected Filter $filter = new Filter(),
    ) {
        foreach ($this->methodParameters as $methodParameter) {
            if ($this->reflector->isOptionsParameter($methodParameter)) {
                $this->addOptionAttributes($methodParameter);
            }
        }

        usort(
            $this->optionAttributes,
            fn (Option $a, Option $b) => $a->names <=> $b->names
        );

        foreach ($this->methodParameters as $methodParameter) {
            if (! $this->reflector->isOptionsParameter($methodParameter)) {
                $this->argumentParameters[] = $methodParameter;
            }
        }
    }

    public function getCommandHelp() : ?Help
    {
        return $this->reflector->getHelpAttribute(
            $this->reflector->getClass($this->commandClass)
        );
    }

    public function getArgumentHelp(int $argumentNumber) : ?Help
    {
        return $this->reflector->getHelpAttribute(
            $this->argumentParameters[$argumentNumber]
        );
    }

    /**
     * @return array<int, ReflectionParameter>
     */
    public function getArgumentParameters() : array
    {
        return $this->argumentParameters;
    }

    protected function addOptionAttributes(
        ReflectionParameter $optionsParameter
    ) : void
    {
        /** @var class-string */
        $optionsClass = $this->reflector->getParameterType($optionsParameter);
        $optionAttributes = $this->reflector->getOptionAttributes($optionsClass);
        $this->optionAttributesByClass[$optionsClass] = $optionAttributes;
        $this->optionAttributes = array_merge(
            $this->optionAttributes,
            array_values($optionAttributes)
        );
    }

    /**
     * @return array<int, Option>
     */
    public function getOptionAttributes() : array
    {
        return $this->optionAttributes;
    }

    /**
     * @param array<int, string> $argv
     * @return mixed[]
     */
    public function parse(array $argv) : array
    {
        $optionParser = new OptionParser(
            $this->optionAttributes,
            $this->reflector,
            $this->filter
        );

        $this->argv = $optionParser($argv);

        $arguments = [];

        foreach ($this->methodParameters as $methodParameter) {
            $this->reflector->isOptionsParameter($methodParameter)
                ? $this->newOptions($methodParameter, $arguments)
                : $this->getArgument($methodParameter, $arguments);
        }

        return $arguments;
    }

    /**
     * @param array<int, mixed> &$arguments
     */
    protected function newOptions(
        ReflectionParameter $optionsParameter,
        array &$arguments
    ) : void
    {
        $optionsClass = $this->reflector->getParameterType($optionsParameter);
        $optionAttributes = $this->optionAttributesByClass[$optionsClass];
        $values = [];

        foreach ($optionAttributes as $name => $option) {
            $values[$name] = $option->getValue();
        }

        /** @var Options */
        $options = new $optionsClass(...$values);
        $arguments[] = $options;
    }

    /**
     * @param array<int, mixed> &$arguments
     */
    protected function getArgument(
        ReflectionParameter $argumentParameter,
        array &$arguments
    ) : void
    {
        $pos = count($arguments);
        $name = $argumentParameter->getName();
        $type = $this->reflector->getParameterType($argumentParameter);

        if (empty($this->argv) && ! $argumentParameter->isOptional()) {
            throw new Exception\ArgumentRequired("Argument {$pos} (\${$name}) is missing.");
        }

        $errmsg = "Argument {$pos} (\${$name}) expected {$type} value";

        if (! $argumentParameter->isVariadic()) {
            $value = array_shift($this->argv);
            $arguments[] = ($this->filter)($value, $type, $errmsg);
            return;
        }

        // variadic; capture all remaining argv
        while (! empty($this->argv)) {
            $value = array_shift($this->argv);
            $arguments[] = ($this->filter)($value, $type, $errmsg);
        }
    }
}
