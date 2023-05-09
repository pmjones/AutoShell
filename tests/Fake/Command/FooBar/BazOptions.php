<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Option;
use AutoShell\Options;

class BazOptions extends Options
{
    public function __construct(

        #[Option('z,zim')]
        public readonly ?bool $zim

    ) {
    }
}
