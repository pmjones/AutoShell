<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Help;

#[Help("Command for qux operations.")]
class Qux
{
    public function __invoke() : int
    {
        return 0;
    }
}
