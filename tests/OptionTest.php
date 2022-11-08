<?php
namespace AutoShell;

class OptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaultNotAllowed()
    {
        $this->expectException(Exception\DefaultNotAllowed::class);
        new Option(
            names: 'f,foo',
            default: 'bar',
        );
    }
}
