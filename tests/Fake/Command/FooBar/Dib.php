<?php
declare(strict_types=1);

namespace AutoShell\Fake\Command\FooBar;

use AutoShell\Help;

#[Help(
    'Dibs an i, with optional alpha, bravo, and charlie behaviors.',
    <<<BODY
    *DESCRIPTION*
        This is a description of the command.

        There are quite a few nuances.

    *EXAMPLES*
        Here are some examples of how to use the command.

        Please use your imagination.

    BODY
)]
class Dib
{
    public function __invoke(
        DibOptions $options,

        #[Help('The i to be dibbed')]
        int $i,

        string $k = 'kay'
    ) : int
    {
        return 0;
    }
}
