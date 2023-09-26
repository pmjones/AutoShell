<?php
declare(strict_types=1);

namespace AutoShell\Exception;

use AutoShell\Exception;

class ClassNotFound extends Exception
{
    public function __construct(string $commandName, string $class)
    {
        $message = "The command '{$commandName}' maps to the class "
            . "'{$class}', which does not exist.";
        parent::__construct($message);
    }
}
