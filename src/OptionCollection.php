<?php
declare(strict_types=1);

namespace AutoShell;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class OptionCollection implements Countable, IteratorAggregate
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
        foreach ($this->attributes as $option) {
            foreach ($option->names as $name) {
                $this->names[$name] = $option;
            }
        }
    }

    /**
     * @return ArrayIterator<int, Option>
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->attributes);
    }

    public function count() : int
    {
        return count($this->attributes);
    }

    public function has(string $name) : bool
    {
        $name = ltrim($name, '-');
        return isset($this->names[$name]);
    }

    public function get(string $name) : Option
    {
        $name = ltrim($name, '-');

        if ($this->has($name)) {
            return $this->names[$name];
        }

        $name = strlen($name) === 1 ? "-{$name}" : "--{$name}";

        throw new Exception\OptionNotDefined(
            "Option {$name} is not defined."
        );
    }
}
