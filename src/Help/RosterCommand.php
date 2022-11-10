<?php
declare(strict_types=1);

namespace AutoShell\Help;

use AutoShell\Options;
use AutoShell\Roster;

class RosterCommand extends HelpCommand
{
    public function __invoke(Options $options) : int
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
        fwrite($this->stdout, $output);
        return 0;
    }
}
