<?php
declare(strict_types=1);

namespace AutoShell;

use ArrayAccess;

class Options implements ArrayAccess
{
    public function __construct(
        protected OptionCollection $optionCollection = new OptionCollection()
    ) {
    }

    public function __get(string $key) : mixed
    {
        return $this->optionCollection->get($key)->getValue();
    }

    public function __set(string $key, mixed $val) : void
    {
        $class = get_class($this);
        throw new Exception\OptionsReadonly("Cannot set {$class}::\${$key}.");
    }

    public function __isset(string $key) : bool
    {
        if (! $this->optionCollection->has($key)) {
            return false;
        }

        return $this->optionCollection->get($key)->getValue() !== null;
    }

    public function __unset(string $key) : void
    {
        $class = get_class($this);
        throw new Exception\OptionsReadonly("Cannot unset {$class}::\${$key}.");
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function offsetGet(mixed $key) : mixed
    {
        return $this->optionCollection->get($key)->getValue();
    }

    /**
     * @param string $key
     * @param mixed $val
     */
    public function offsetSet(mixed $key, mixed $val) : void
    {
        $class = get_class($this);
        throw new Exception\OptionsReadonly("Cannot set {$class}::\${$key}.");
    }

    /**
     * @param string $key
     */
    public function offsetExists(mixed $key) : bool
    {
        if (! $this->optionCollection->has($key)) {
            return false;
        }

        return $this->optionCollection->get($key)->getValue() !== null;
    }

    /**
     * @param string $key
     */
    public function offsetUnset(mixed $key) : void
    {
        $class = get_class($this);
        throw new Exception\OptionsReadonly("Cannot unset {$class}::\${$key}.");
    }

    /**
     * @return array<string, mixed>
     */
    public function asArray() : array
    {
        $values = [];

        foreach ($this->optionCollection as $name => $option) {
            foreach ($option->names as $name) {
                $values[$name] = $option->getValue();
            }
        }

        /** @var array<string, mixed> */
        return $values;
    }
}
