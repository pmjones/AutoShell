<?php
declare(strict_types=1);

namespace AutoShell;

class OptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaultNotAllowed() : void
    {
        $this->expectException(Exception\DefaultNotAllowed::class);
        new Option(
            names: 'f,foo',
            default: 'bar',
        );
    }
}
