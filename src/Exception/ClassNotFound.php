<?php
declare(strict_types=1);

namespace AutoShell\Exception;

use AutoShell\Exception;

class ClassNotFound extends Exception
{
    public static function new(
        string $commandName,
        string $class
    ): self {
        $message = "The command '{$commandName}' maps to the class "
            . "'{$class}', which does not exist.";

        return new self($message);
    }
}
