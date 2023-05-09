<?php
declare(strict_types=1);

namespace AutoShell\Help;

use AutoShell\Roster;

class RosterCommand extends HelpCommand
{
    public function __invoke() : int
    {
        $output = (string) $this->config->help;

        $roster = new Roster($this->config);
        $commands = $roster();

        foreach ($commands as $name => $info) {
            if (trim($info) === '') {
                $info = "No help available.";
            }

            $output .= $this->format->bold($name) . PHP_EOL
                . "    {$info}" . PHP_EOL . PHP_EOL;
        }

        if (empty($commands)) {
            $output .= "No commands found." . PHP_EOL
                . "Namespace: {$this->config->namespace}" . PHP_EOL
                . "Directory: {$this->config->directory}" . PHP_EOL;
        }

        ($this->stdout)($output);
        return 0;
    }
}
