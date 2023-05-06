<?php
declare(strict_types=1);

namespace AutoShell\Help;

use AutoShell\Roster;

class RosterCommand extends HelpCommand
{
    public function __invoke() : int
    {
        $output = '';
        $roster = new Roster($this->config);
        $commands = $roster();

        foreach ($commands as $name => $info) {
            if (trim($info) === '') {
                $info = "No help available.";
            }
            $output .= $this->format->bold($name) . PHP_EOL
                . "    {$info}" . PHP_EOL . PHP_EOL;
        }

        $header = (string) $this->config->header;

        if ($header !== '') {
            ($this->stdout)($header);
        }

        ($this->stdout)($output);
        return 0;
    }
}
