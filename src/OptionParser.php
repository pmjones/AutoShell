<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionParameter;
use ReflectionClass;

class OptionParser
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
     * @param array<int, Option> $optionCollection
     */
    public function __construct(
        protected array $optionCollection,
        protected Reflector $reflector,
        protected Filter $filter,
    ) {
        foreach ($this->optionCollection as $option) {
            foreach ($option->names as $name) {
                if (isset($this->optionsByName[$name])) {
                    throw new Exception\OptionAlreadyDefined("Option '{$name}' is already defined.");
                }
                $this->optionsByName[$name] = $option;
            }
        }
    }

    /**
     * @param array<int, string> $argv
     * @return array<int, string>
     */
    public function __invoke(array $argv) : array
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

        /**
         * `$chars` will always have more than one element,
         * hence `array_pop` will never return `null`
         */
        $chars = str_split($name);
        /** @var string $final */
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
}
