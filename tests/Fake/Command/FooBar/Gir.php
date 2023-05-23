<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Help;

#[Help("Command for Gir.")]
class Gir
{
    public function __invoke(
        BazOptions $bazOptions,
        DibOptions $dibOptions,
        string $doom
    ) : int
    {
        return 0;
    }
}
