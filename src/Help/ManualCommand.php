<?php
declare(strict_types=1);

namespace AutoShell\Help;

use AutoShell\Manual;

class ManualCommand extends HelpCommand
{
    /**
     * @param class-string $class
     */
    public function __invoke(
        string $commandName,
        string $class,
        string $method
    ) : int
    {
        $output = (string) $this->config->help;
        $manual = new Manual($this->reflector, $this->format);
        $output .= $manual($commandName, $class, $method);
        ($this->stdout)($output);
        return 0;
    }
}
