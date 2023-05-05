<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

class Baz
{
    public function __invoke(
        BazOptions $options,
        int $i,
        ...$tail
    ) : int
    {
        return 0;
    }
}
