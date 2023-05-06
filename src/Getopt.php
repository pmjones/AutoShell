<?php
declare(strict_types=1);

namespace AutoShell;

class Getopt
{
    /**
     * @var array<string, Option>
     */
    protected array $names = [];

    /**
     * @var array<string, Option>
     */
    protected array $optionAttributes = [];

    public function __construct(protected Filter $filter = new Filter())
    {
    }

    protected function getOptionByName(string $name) : Option
    {
        $name = ltrim($name, '-');

        if (isset($this->names[$name])) {
            return $this->names[$name];
        }

        $name = strlen($name) === 1 ? "-{$name}" : "--{$name}";

        throw new Exception\OptionNotDefined(
            "Option {$name} is not defined."
        );

    }

    /**
     * @param array<string, Option> &$optionAttributes
     * @param array<int, string> $input
     * @return array<int, mixed>
     */
    public function parse(
        array &$optionAttributes,
        array $input
    ) : array
    {
        $this->optionAttributes = &$optionAttributes;

        foreach ($this->optionAttributes as $option) {
            foreach ($option->names as $name) {
                $this->names[$name] = $option;
            }
        }

        // flag to say when we've reached the end of options
        $done = false;

        // arguments
        $arguments = [];

        // loop through the input values to be parsed
        while ($input) {

            // shift each element from the top of the $input source
            $curr = array_shift($input);

            // after a plain double-dash, all values are arguments (not options)
            if ($curr == '--') {
                $done = true;
                continue;
            }

            if ($done) {
                $arguments[] = $curr;
                continue;
            }

            // long option?
            if (substr($curr, 0, 2) == '--') {
                $this->longOption($input, ltrim($curr, '-'));
                continue;
            }

            // short option?
            if (substr($curr, 0, 1) == '-') {
                $this->shortOption($input, ltrim($curr, '-'));
                continue;
            }

            // argument
            $arguments[] = $curr;
        }

        return $arguments;
    }

    /**
     * @param array<int, string> &$input
     */
    protected function longOption(
        array &$input,
        string $name
    ) : void
    {
        $pos = strpos($name, '=');

        if ($pos !== false) {
            $value = substr($name, $pos + 1);
            $name = substr($name, 0, $pos);
            $this->getOptionByName($name)->equals($value, $this->filter);
            return;
        }

        $this->getOptionByName($name)->capture($input, $this->filter);
    }

    /**
     * @param array<int, string> &$input
     */
    protected function shortOption(
        array &$input,
        string $name
    ) : void
    {
        if (strlen($name) == 1) {
            $this->getOptionByName($name)->capture($input, $this->filter);
            return;
        }

        $chars = str_split($name);
        $final = array_pop($chars);

        foreach ($chars as $char) {
            $this->getOptionByName($char)->equals('', $this->filter);
        }

        $this->getOptionByName($final)->capture($input, $this->filter);
    }
}
