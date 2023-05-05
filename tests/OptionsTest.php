<?php
declare(strict_types=1);

namespace AutoShell;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    public function test() : void
    {
        $foo = new Option('f,foo', argument: Option::VALUE_REQUIRED);
        $foo->equals('fooval', new Filter());
        $bar = new Option('b,bar');
        $options = new FakeOptions(new OptionCollection(['foo' => $foo, 'bar' => $bar]));

        $this->assertSame('fooval', $options->foo);
        $this->assertSame(null, $options->bar);
    }
}
