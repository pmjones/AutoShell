<?php
declare(strict_types=1);

namespace AutoShell\Help;

use AutoShell\Config;
use AutoShell\Format;

abstract class HelpCommand
{
    /**
     * @param callable $stdout
     */
    public function __construct(
        protected Config $config,
        protected Format $format,
        protected mixed $stdout,
    ) {
    }
}
