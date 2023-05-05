<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;

class Options
{
    /**
     * @param array<string, Option> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $rc = new ReflectionClass($this);

        foreach ($attributes as $property => $option) {
            $rp = $rc->getProperty($property);
            $rp->setAccessible(true);
            $rp->setValue($this, $option->getValue());
        }
    }
}
