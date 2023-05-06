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
        return ($type === null)
            ? 'mixed'
            : $type->getName(); // @phpstan-ignore-line
    }

    public function isOptionsClass(string $class) : bool
    {
        return is_a($class, Options::class, true);
    }

    /**
     * @return class-string
     */
    public function getOptionsClass(ReflectionMethod $rm) : string
    {
        $optionsClass = '';
        $parameters = $rm->getParameters();

        foreach ($parameters as $parameter) {
            $type = $this->getParameterType($parameter);
            if ($this->isOptionsClass($type)) {
                $optionsClass = $type;
                break;
            }
        }

        /** @var class-string */
        return $optionsClass;
    }

    /**
     * @param class-string $optionsClass
     * @return array<string, Option>
     */
    public function getOptionAttributes(string $optionsClass) : array
    {
        if (! $optionsClass) {
            return [];
        }

        $optionAttributes = [];
        $properties = $this->getClass($optionsClass)->getProperties();

        foreach ($properties as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($attribute->getName() === Option::class) {
                    /** @var Option */
                    $option = $attribute->newInstance();
                    $option->setType((string) $property->getType());
                    $optionAttributes[$property->getName()] = $option;
                }
            }
        }

        return $optionAttributes;
    }

    /**
     * @return ReflectionParameter[]
     */
    public function getArgumentParameters(ReflectionMethod $rm) : array
    {
        $argumentParameters = [];
        $rps = $rm->getParameters();

        while (! empty($rps)) {
            $rp = array_shift($rps);
            if ($this->isOptionsClass($this->getParameterType($rp))) {
                break;
            }
        }

        foreach ($rps as $rp) {
            $argumentParameters[] = $rp;
        }

        return $argumentParameters;
    }

    public function getHelpAttribute(
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
        return $this->isOptionsclass((string) $rp->getType());
    }

    public function getSignature(string $class, string $method) : Signature
    {
        $rc = $this->getClass($class);
        $rm = $this->getMethod($rc, $method);

        $optionsPosition = null;
        $optionsClass = '';
        $optionAttributes = [];
        $argumentParameters = [];

        foreach ($rm->getParameters() as $position => $rp) {
            if ($this->isOptionsParameter($rp)) {
                $optionsClass = (string) $rp->getType();
                $optionsPosition = $position;
            }
            $argumentParameters[] = $rp;
        }

        return new Signature(
            $argumentParameters,
            $optionsPosition,
            $optionsClass,
            $optionAttributes,
            $this,
        );
    }
}
