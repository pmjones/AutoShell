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

    /**
     * @return Option[]
     */
    public function getOptionAttributes(ReflectionClass $rc) : array
    {
        $attributes = [];

        foreach ($rc->getAttributes() as $attribute) {
            if ($attribute->getName() === Option::CLASS) {
                $attributes[] = $attribute->newInstance();
            }
        }

        /** @var Option[] */
        return $attributes;
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
            if (static::getParameterType($rp) === Options::CLASS) {
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
            if ($attribute->getName() === Help::CLASS) {
                /** @var Help */
                return $attribute->newInstance();
            }
        }

        return null;
    }
}
