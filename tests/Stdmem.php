<?php
declare(strict_types=1);

namespace AutoShell;

use Stringable;

class Stdmem implements Stringable
{
    protected string $output = '';

    public function __construct(protected Format $format = new Format())
    {
    }

    public function __invoke(string $output) : void
    {
        $this->output .= $output;
    }

    public function __toString() : string
    {
        return $this->format->strip($this->output);
    }
}
