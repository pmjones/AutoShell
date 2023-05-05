<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;

class Options
{
    /**
     * @param array<string, Option> $options
     */
    public function __construct(array $options = [])
    {
        $rc = new ReflectionClass($this);

        foreach ($options as $property => $option) {
            $rp = $rc->getProperty($property);
            $rp->setAccessible(true);
            $rp->setValue($this, $option->getValue());
        }
    }
}
