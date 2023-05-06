<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;

class Options
{
    /**
     * @param array<string, Option> $optionAttributes
     */
    public function __construct(array $optionAttributes = [])
    {
        $rc = new ReflectionClass($this);

        foreach ($optionAttributes as $property => $option) {
            $rp = $rc->getProperty($property);
            $rp->setAccessible(true);
            $rp->setValue($this, $option->getValue());
        }
    }
}
