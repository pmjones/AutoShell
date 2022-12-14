<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Option;
use AutoShell\Options;

#[Option('z,zim')]
class Baz
{
    public function __invoke(
        Options $options,
        int $i,
        ...$tail
    ) : int
    {
        return 0;
    }
}
