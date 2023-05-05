<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;

class Options
{
    public function __construct(OptionCollection $optionCollection = new OptionCollection())
    {
        $rc = new ReflectionClass($this);

        foreach ($optionCollection as $property => $option) {
            $rp = $rc->getProperty($property);
            $rp->setAccessible(true);
            $rp->setValue($this, $option->getValue());
        }
    }
}
