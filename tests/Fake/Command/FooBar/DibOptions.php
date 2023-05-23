<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Option;
use AutoShell\Options;

class DibOptions implements Options
{
    public function __construct(

        #[Option(
            'a,alpha',
            help: "The alpha option."
        )]
        public readonly ?bool $alpha,

        #[Option('b,bravo',
            mode: Option::VALUE_REQUIRED,
        )]
        public readonly ?string $bravo,

        #[Option('c,charlie',
            mode: Option::VALUE_OPTIONAL,
            default: 'delta',
        )]
        public readonly ?string $charlie,

    ) {
    }
}
