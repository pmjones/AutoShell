<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Help;
use AutoShell\Option;
use AutoShell\Options;

#[Help(
    'Dibs an i, with optional alpha, bravo, and charlie behaviors.',
    <<<BODY
    *DESCRIPTION*
        This is a description of the command.

        There are quite a few nuances.

    *EXAMPLES*
        Here are some examples of how use the command.

        Please use your imagination.

    BODY
)]
#[Option('a,alpha',
    help: "The alpha option."
)]
#[Option('b,bravo',
    argument: Option::VALUE_REQUIRED,
    argname: 'bval'
)]
#[Option('c,charlie',
    argument: Option::VALUE_OPTIONAL,
    default: 'delta',
)]
class Dib
{
    public function __invoke(
        Options $options,

        #[Help('The i to be dibbed')]
        int $i,

        string $k = 'kay'
    ) : int
    {
        return 0;
    }
}
