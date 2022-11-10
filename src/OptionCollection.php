<?php
declare(strict_types=1);

namespace AutoShell;

use ArrayIterator;

class OptionCollection extends ArrayIterator
{
    /**
     * @var Option[]
     */
    protected array $names = [];

    /**
     * @param Option[] $attributes
     */
    public function __construct(protected array $attributes = [])
    {
        $names = [];
        foreach ($this->attributes as $option) {
            foreach ($option->names as $name) {
                $names[$name] = $option;
            }
        }
        parent::__construct($names);
    }

    public function has(string $name) : bool
    {
        $name = ltrim($name, '-');
        return $this->offsetExists($name);
    }

    public function get(string $name) : Option
    {
        $name = ltrim($name, '-');

        if ($this->has($name)) {
            return $this->offsetGet($name);
        }

        $name = strlen($name) === 1 ? "-{$name}" : "--{$name}";

        throw new Exception\OptionNotDefined(
            "Option {$name} is not defined."
        );
    }
}
