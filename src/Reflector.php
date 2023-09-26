<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

class Reflector
{
    /**
     * @param class-string $class
     * @return ReflectionClass<object>
     */
    public function getClass(string $class) : ReflectionClass
    {
        return new ReflectionClass($class);
    }

    /**
     * @param ReflectionClass<object> $rc
     */
    public function getMethod(ReflectionClass $rc, string $method) : ReflectionMethod
    {
        return $rc->getMethod($method);
    }

    public function getParameterType(ReflectionParameter $rp) : string
    {
        $name = $rp->getName();
        $type = $rp->getType();

        /** @phpstan-ignore-next-line */
        return trim((string) $type->getName(), '?');
    }

    public function getPropertyType(ReflectionProperty $rp) : string
    {
        $name = $rp->getName();
        $type = $rp->getType();

        /** @phpstan-ignore-next-line */
        return trim((string) $type->getName(), '?');
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
                    $args = $attribute->getArguments();
                    $args['type'] = $this->getPropertyType($property);
                    $args['valname'] = $property->getName();
                    $optionCollection[$property->getName()] = new Option(...$args);
                }
            }
        }

        return $optionCollection;
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod|ReflectionParameter $spec
     */
    public function getHelp(
        ReflectionClass|ReflectionMethod|ReflectionParameter $spec,
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
