<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Option;
use AutoShell\Options;

class DibOptions extends Options
{
    #[Option(
        'a,alpha',
        help: "The alpha option."
    )]
    public readonly ?bool $alpha;

    #[Option('b,bravo',
        argument: Option::VALUE_REQUIRED,
        argname: 'bval'
    )]
    public readonly ?string $bravo;

    #[Option('c,charlie',
        argument: Option::VALUE_OPTIONAL,
        default: 'delta',
    )]
    public readonly ?string $charlie;
}
