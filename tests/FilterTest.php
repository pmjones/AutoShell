<?php
namespace AutoShell;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $this->filter = new Filter();
    }

    public function testToArray()
    {
        $expect = ['1', '2', '3'];

        $actual = ($this->filter)('1,2,3', 'array', 'Expected array');
        $this->assertSame($expect, $actual);

        $actual = ($this->filter)(['1', '2', '3'], 'array', 'Expected array');
        $this->assertSame($expect, $actual);
    }

    public function testToBool_true()
    {
        $expect = true;

        $actual = ($this->filter)('Yes', 'bool', 'Expected bool');
        $this->assertSame($expect, $actual);

        $actual = ($this->filter)(true, 'bool', 'Expected bool');
        $this->assertSame($expect, $actual);

        $this->expectException(Exception\ArgumentInvalid::CLASS);
        ($this->filter)('x', 'bool', 'Expected bool');
    }

    public function testToBool_false()
    {
        $expect = false;

        $actual = ($this->filter)('No', 'bool', 'Expected bool');
        $this->assertSame($expect, $actual);

        $actual = ($this->filter)(false, 'bool', 'Expected bool');
        $this->assertSame($expect, $actual);

        $this->expectException(Exception\ArgumentInvalid::CLASS);
        ($this->filter)('x', 'bool', 'Expected bool');
    }

    public function testToInt()
    {
        $expect = 1;

        $actual = ($this->filter)('1', 'int', 'Expected int');
        $this->assertSame($expect, $actual);

        $actual = ($this->filter)(1, 'int', 'Expected int');
        $this->assertSame($expect, $actual);

        $this->expectException(Exception\ArgumentInvalid::CLASS);
        ($this->filter)('x', 'int', 'Expected int');
    }

    public function testToFloat()
    {
        $expect = 1.23;

        $actual = ($this->filter)('1.23', 'float', 'Expected float');
        $this->assertSame($expect, $actual);

        $actual = ($this->filter)(1.23, 'float', 'Expected float');
        $this->assertSame($expect, $actual);

        $this->expectException(Exception\ArgumentInvalid::CLASS);
        ($this->filter)('x', 'float', 'Expected float');
    }

    public function testToMixed()
    {
        $expect = 'abc';
        $actual = ($this->filter)($expect, 'mixed', 'Expected mixed');
        $this->assertSame($expect, $actual);

        $expect = 123;
        $actual = ($this->filter)($expect, 'mixed', 'Expected mixed');
        $this->assertSame($expect, $actual);

        $expect = 4.56;
        $actual = ($this->filter)($expect, 'mixed', 'Expected mixed');
        $this->assertSame($expect, $actual);
    }

    public function testToString()
    {
        $expect = 'abc';
        $actual = ($this->filter)($expect, 'string', 'Expected string');
        $this->assertSame('abc', $actual);

        $expect = 123;
        $actual = ($this->filter)($expect, 'string', 'Expected string');
        $this->assertSame('123', $actual);

        $expect = 4.56;
        $actual = ($this->filter)($expect, 'string', 'Expected string');
        $this->assertSame('4.56', $actual);
    }
}
