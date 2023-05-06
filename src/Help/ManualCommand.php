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
        $manual = new Manual();
        $output = $manual($commandName, $class, $method);
        $header = (string) $this->config->header;

        if ($header !== '') {
            ($this->stdout)($header);
        }

        ($this->stdout)($output);
        return 0;
    }
}
