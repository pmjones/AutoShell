<?php
declare(strict_types=1);

namespace AutoShell;

class Getopt
{
    public function __construct(protected Filter $filter = new Filter())
    {
    }

    /**
     * @param array<int, string> $input
     * @return array<int, mixed>
     */
    public function parse(
        OptionCollection $optionCollection,
        array $input
    ) : array
    {
        // flag to say when we've reached the end of options
        $done = false;

        // arguments
        $arguments = [];

        // loop through a copy of the input values to be parsed
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
                $this->longOption($input, $optionCollection, ltrim($curr, '-'));
                continue;
            }

            // short option?
            if (substr($curr, 0, 1) == '-') {
                $this->shortOption($input, $optionCollection, ltrim($curr, '-'));
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
        OptionCollection $optionCollection,
        string $name
    ) : void
    {
        $pos = strpos($name, '=');

        if ($pos !== false) {
            $value = substr($name, $pos + 1);
            $name = substr($name, 0, $pos);
            $optionCollection->get($name)->equals($value, $this->filter);
            return;
        }

        $optionCollection->get($name)->capture($input, $this->filter);
    }

    /**
     * @param array<int, string> &$input
     */
    protected function shortOption(
        array &$input,
        OptionCollection $optionCollection,
        string $name
    ) : void
    {
        if (strlen($name) == 1) {
            $optionCollection->get($name)->capture($input, $this->filter);
            return;
        }

        $chars = str_split($name);
        $final = array_pop($chars);

        foreach ($chars as $char) {
            $optionCollection->get($char)->equals('', $this->filter);
        }

        $optionCollection->get($final)->capture($input, $this->filter);
    }
}
