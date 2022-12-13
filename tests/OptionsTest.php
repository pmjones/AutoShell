<?php
declare(strict_types=1);

namespace AutoShell;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    protected $options;

    protected function setUp() : void
    {
        $foo = new Option('f,foo', argument: Option::VALUE_REQUIRED);
        $foo->equals('fooval', new Filter());
        $bar = new Option('b,bar');
        $this->options = new Options(new OptionCollection([$foo, $bar]));
    }

    public function test__get()
    {
        $this->assertSame('fooval', $this->options->foo);
    }

    public function test__set()
    {
        $this->expectException(Exception\OptionsReadonly::CLASS);
        $this->options->foo = 'foovaltwo';
    }

    public function test__isset()
    {
        $this->assertTrue(isset($this->options->foo));
        $this->assertFalse(isset($this->options->bar));
        $this->assertFalse(isset($this->options->nonesuch));
    }

    public function test__unset()
    {
        $this->expectException(Exception\OptionsReadonly::CLASS);
        unset($this->options->foo);
    }

    public function testOffsetGet()
    {
        $this->assertSame('fooval', $this->options['foo']);
    }

    public function testOffsetSet()
    {
        $this->expectException(Exception\OptionsReadonly::CLASS);
        $this->options['foo'] = 'foovaltwo';
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->options['foo']));
        $this->assertFalse(isset($this->options['bar']));
        $this->assertFalse(isset($this->options['nonesuch']));
    }

    public function testOffsetUnset()
    {
        $this->expectException(Exception\OptionsReadonly::CLASS);
        unset($this->options['foo']);
    }

    public function testGet()
    {
        $actual = $this->options->asArray();
        $expect = [
            'f' => 'fooval',
            'foo' => 'fooval',
            'b' => null,
            'bar' => null,
        ];
        $this->assertSame($expect, $actual);
    }
}
