<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class Reflector
{
    /**
     * @param class-string $class
     */
    public function getClass(string $class) : ReflectionClass
    {
        return new ReflectionClass($class);
    }

    public function getMethod(
        ReflectionClass $rc,
        string $method
    ) : ReflectionMethod
    {
        return $rc->getMethod($method);
    }

    public function getParameterType(ReflectionParameter $rp) : string
    {
        $name = $rp->getName();
        $type = $rp->getType();
        return (string) $type->getName(); // @phpstan-ignore-line
    }

    protected function isOptionsClass(string $class) : bool
    {
        return is_subclass_of($class, Options::class, true);
    }

    /**
     * @param class-string $optionsClass
     * @return array<string, Option>
     */
    public function getOptionCollection(string $optionsClass) : array
    {
        $optionCollection = [];
        $properties = $this->getClass($optionsClass)->getProperties();

        foreach ($properties as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($attribute->getName() === Option::class) {
                    /** @var Option */
                    $option = $attribute->newInstance();
                    $option->setType((string) $property->getType());
                    $optionCollection[$property->getName()] = $option;
                }
            }
        }

        return $optionCollection;
    }

    public function getHelp(
        ReflectionClass|ReflectionMethod|ReflectionParameter $spec
    ) : ?Help
    {
        foreach ($spec->getAttributes() as $attribute) {
            if ($attribute->getName() === Help::class) {
                /** @var Help */
                return $attribute->newInstance();
            }
        }

        return null;
    }

    public function isCommandClass(string $class) : bool
    {
        if (
            ! class_exists($class)
            || interface_exists($class)
            || trait_exists($class)
            || $this->isOptionsClass($class)
        ) {
            return false;
        }

        return ! $this->getClass($class)->isAbstract();
    }

    public function isOptionsParameter(ReflectionParameter $rp) : bool
    {
        return $this->isOptionsClass((string) $rp->getType());
    }

    /**
     * @param class-string $class
     */
    public function getSignature(string $class, string $method) : Signature
    {
        $rc = $this->getClass($class);
        $rm = $this->getMethod($rc, $method);

        return new Signature(
            $class,
            $method,
            $rm->getParameters(),
            $this,
            new Filter(),
        );
    }
}
