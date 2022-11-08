<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Help;
use AutoShell\Options;

#[Help("Command for qux operations.")]
class Qux
{
    public function __invoke(
        Options $options
    ) : int
    {
        return 0;
    }
}
