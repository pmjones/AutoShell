<?php
namespace AutoShell\Help;

use AutoShell\Config;
use AutoShell\Format;

abstract class HelpCommand
{
    /**
     * @param resource $stdout
     */
    public function __construct(
        protected Config $config,
        protected Format $format = new Format(),
        protected mixed $stdout = STDOUT
    ) {
    }
}
