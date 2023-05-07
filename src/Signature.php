<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionParameter;

class Signature
{
    /**
     * @var array<int, string>
     */
    protected array $argv = [];

    /**
     * @var array<string, Option>
     */
    protected array $optionsByName = [];

    /**
     * @param array <int, ReflectionParameter> $argumentParameters
     * @param array <string, Option> $optionAttributes
     */
    public function __construct(
        protected array $argumentParameters,
        protected ?int $optionsPosition,
        protected string $optionsClass,
        protected array $optionAttributes,
        protected Reflector $reflector = new Reflector(),
        protected Filter $filter = new Filter(),
    ) {
        foreach ($this->optionAttributes as $option) {
            foreach ($option->names as $name) {
                $this->optionsByName[$name] = $option;
            }
        }
    }

    /**
     * @param array<int, string> $argv
     * @return mixed[]
     */
    public function parse(array $argv) : array
    {
        $this->parseOptions($argv);

        $arguments = [];

        foreach ($this->argumentParameters as $position => $argumentParameter) {
            if ($position === $this->optionsPosition) {
                $optionsClass = $this->optionsClass;
                $options = new $optionsClass($this->optionAttributes);
                $arguments[] = $options;
            } else {
                $this->getArgument($argumentParameter, $arguments);
            }
        }

        return $arguments;
    }

    /**
     * @param array<int, string> $argv
     * @return array<int, string>
     */
    public function parseOptions(array $argv) : array
    {
        $this->argv = $argv;

        // done parsing options?
        $done = false;

        // retained arguments
        $keep = [];

        // loop through the argv values to be parsed
        while ($this->argv) {

            // shift each element from the top of $this->argv
            $curr = array_shift($this->argv);

            // after a plain double-dash, all values are arguments (not options)
            if ($curr == '--') {
                $done = true;
                continue;
            }

            if ($done) {
                $keep[] = $curr;
                continue;
            }

            // long option?
            if (substr($curr, 0, 2) == '--') {
                $this->longOption(ltrim($curr, '-'));
                continue;
            }

            // short option?
            if (substr($curr, 0, 1) == '-') {
                $this->shortOption(ltrim($curr, '-'));
                continue;
            }

            // retain as argument
            $keep[] = $curr;
        }

        // reset to retained arguments
        $this->argv = $keep;
        return $keep;
    }

    protected function longOption(string $name) : void
    {
        $pos = strpos($name, '=');

        if ($pos !== false) {
            $value = substr($name, $pos + 1);
            $name = substr($name, 0, $pos);
            $this->getOptionByName($name)->equals($value, $this->filter);
            return;
        }

        $this->getOptionByName($name)->capture($this->argv, $this->filter);
    }

    protected function shortOption(string $name) : void
    {
        if (strlen($name) == 1) {
            $this->getOptionByName($name)->capture($this->argv, $this->filter);
            return;
        }

        $chars = str_split($name);
        $final = array_pop($chars);

        foreach ($chars as $char) {
            $this->getOptionByName($char)->equals('', $this->filter);
        }

        $this->getOptionByName($final)->capture($this->argv, $this->filter);
    }

    protected function getOptionByName(string $name) : Option
    {
        $name = ltrim($name, '-');

        if (isset($this->optionsByName[$name])) {
            return $this->optionsByName[$name];
        }

        $name = strlen($name) === 1 ? "-{$name}" : "--{$name}";

        throw new Exception\OptionNotDefined(
            "Option {$name} is not defined."
        );
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
