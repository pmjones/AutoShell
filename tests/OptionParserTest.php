<?php
declare(strict_types=1);

namespace AutoShell;

class OptionParserTest extends \PHPUnit\Framework\TestCase
{
    protected Reflector $reflector;

    protected Filter $filter;

    protected function setUp() : void
    {
        $this->reflector = new Reflector();
        $this->filter = new Filter();
    }

    /**
     * @param Option[] $optionCollection
     * @param array<int, string> $input
     * @return mixed[]
     */
    protected function parse(array $optionCollection, array $input) : array
    {
        $optionParser = new OptionParser($optionCollection, $this->reflector, $this->filter);
        return $optionParser($input);
    }

    /**
     * @param mixed[] $expect
     * @param array<string, Option> $options
     */
    protected function assertOptionValues(array $expect, array $options) : void
    {
        $actual = [];

        foreach ($options as $key => $option) {
            $actual[$key] = $option->getValue();
        }

        $this->assertSame($expect, $actual);
    }

    public function testNoOptions() : void
    {
        $options = [];
        $input = ['abc', 'def'];
        $arguments = $this->parse($options, $input);
        $this->assertCount(0, $options);
        $expect = ['abc', 'def'];
        $this->assertSame($expect, $input);
    }

    public function testUndefinedOption() : void
    {
        $options = [];
        $input = ['-z', 'def'];
        $this->expectException(Exception\OptionNotDefined::class);
        $this->expectExceptionMessage("-z is not defined.");
        $this->parse($options, $input);
    }

    public function testOptionAlreadyDefined() : void
    {
        $options = [
            'foo1' => new Option('f,foo'),
            'foo2' => new Option('f'),
        ];
        $input = [];
        $this->expectException(Exception\OptionAlreadyDefined::class);
        $this->expectExceptionMessage("Option 'f' is already defined.");
        $this->parse($options, $input);
    }

    public function testLongRejected() : void
    {
        $options = [
            'foo_bar' => new Option('foo-bar'),
        ];
        $input = ['--foo-bar'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo_bar' => true];
        $this->assertOptionValues($expect, $options);

        $options = [
            'foo_bar' => new Option('foo-bar'),
        ];
        $input = ['--foo-bar=baz'];
        $this->expectException(Exception\ArgumentRejected::class);
        $this->expectExceptionMessage("--foo-bar does not accept a value.");
        $this->parse($options, $input);
    }

    public function testLongRequired() : void
    {
        // '=' as separator
        $options = [
            'foo_bar' => new Option('foo-bar', mode: Option::VALUE_REQUIRED)
        ];
        $input = ['--foo-bar=baz'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo_bar' => 'baz'];
        $this->assertOptionValues($expect, $options);

        // ' ' as separator
        $options = [
            'foo_bar' => new Option('foo-bar', mode: Option::VALUE_REQUIRED)
        ];
        $input = ['--foo-bar', 'baz'];
        $arguments = $this->parse($options, $input);
        $this->assertOptionValues($expect, $options);

        // missing required value
        $options = [
            'foo_bar' => new Option('foo-bar', mode: Option::VALUE_REQUIRED)
        ];
        $input = ['--foo-bar'];
        $this->expectException(Exception\ArgumentRequired::class);
        $this->expectExceptionMessage("--foo-bar requires a value.");
        $this->parse($options, $input);
    }

    public function testLongOptional() : void
    {
        $options = [
            'foo_bar' => new Option('foo-bar', mode: Option::VALUE_OPTIONAL)
        ];
        $input = ['--foo-bar'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo_bar' => true];
        $this->assertOptionValues($expect, $options);

        $options = [
            'foo_bar' => new Option('foo-bar', mode: Option::VALUE_OPTIONAL)
        ];
        $input = ['--foo-bar=baz'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo_bar' => 'baz'];
        $this->assertOptionValues($expect, $options);
    }

    public function testLongMultiple() : void
    {
        $options = [
            'foo_bar' => new Option('foo-bar', mode: Option::VALUE_OPTIONAL, multiple: true)
        ];

        $input = [
            '--foo-bar',
            '--foo-bar',
            '--foo-bar=baz',
            '--foo-bar=dib',
            '--foo-bar'
        ];
        $arguments = $this->parse($options, $input);
        $expect = ['foo_bar' => [true, true, 'baz', 'dib', true]];
        $this->assertOptionValues($expect, $options);
    }

    public function testShortRejected() : void
    {
        $options = [
            'foo' => new Option('f')
        ];
        $input = ['-f'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo' => true];
        $this->assertOptionValues($expect, $options);

        $options = [
            'foo' => new Option('f')
        ];
        $input = ['-f', 'baz'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo' => true];
        $this->assertOptionValues($expect, $options);
        $this->assertSame(['baz'], $arguments);
    }

    public function testShortRequired() : void
    {
        $options = [
            'foo' => new Option('f', mode: Option::VALUE_REQUIRED)
        ];
        $input = ['-f', 'baz'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo' => 'baz'];
        $this->assertOptionValues($expect, $options);

        $options = [
            'f' => new Option('f', mode: Option::VALUE_REQUIRED)
        ];
        $input = ['-f'];
        $this->expectException(Exception\ArgumentRequired::class);
        $this->expectExceptionMessage("-f requires a value.");
        $this->parse($options, $input);
    }

    public function testShortOptional() : void
    {
        $options = [
            'foo' => new Option('f', mode: Option::VALUE_OPTIONAL)
        ];
        $input = ['-f'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo' => true];
        $this->assertOptionValues($expect, $options);

        $options = [
            'foo' => new Option('f', mode: Option::VALUE_OPTIONAL)
        ];
        $input = ['-f', 'baz'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo' => 'baz'];
        $this->assertOptionValues($expect, $options);

        $options = [
            'foo' => new Option('f', mode: Option::VALUE_OPTIONAL, default: 'zim')
        ];
        $input = ['-f'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo' => 'zim'];
        $this->assertOptionValues($expect, $options);
    }

    public function testShortMultiple() : void
    {
        $options = [
            'foo' => new Option('f', mode: Option::VALUE_OPTIONAL, multiple: true)
        ];

        $input = ['-f', '-f', '-f', 'baz', '-f', 'dib', '-f'];
        $arguments = $this->parse($options, $input);
        $expect = ['foo' => [true, true, 'baz', 'dib', true]];
        $this->assertOptionValues($expect, $options);
    }

    public function testShortCluster() : void
    {
        $options = [
            'foo' => new Option('f'),
            'baz' => new Option('b'),
            'zim' => new Option('z'),
        ];

        $input = ['-fbz'];
        $arguments = $this->parse($options, $input);
        $expect = [
            'foo' => true,
            'baz' => true,
            'zim' => true,
        ];
        $this->assertOptionValues($expect, $options);
    }

    public function testShortClusterRequired() : void
    {
        $options = [
            'f' => new Option('f'),
            'b' => new Option('b', mode: Option::VALUE_REQUIRED),
            'z' => new Option('z'),
        ];

        $input = ['-fbz'];
        $this->expectException(Exception\ArgumentRequired::class);
        $this->expectExceptionMessage("-b requires a value.");
        $this->parse($options, $input);
    }

    public function testParseAndGet() : void
    {
        $options = [
            'foo_bar' => new Option('foo-bar', mode: Option::VALUE_REQUIRED),
            'baz' => new Option('b'),
            'zim' => new Option('z', mode: Option::VALUE_OPTIONAL),
        ];

        $input = [
            'abc',
            '--foo-bar=zim',
            'def',
            '-z',
            'qux',
            '-b',
            'gir',
            '--',
            '--after-double-dash=123',
            '-n',
            '456',
            'ghi',
        ];

        $arguments = $this->parse($options, $input);

        $expectOptv = [
            'foo_bar' => 'zim',
            'baz' => true,
            'zim' => 'qux',
        ];

        $expectArgv = [
            'abc',
            'def',
            'gir',
            '--after-double-dash=123',
            '-n',
            '456',
            'ghi',
        ];

        $this->assertOptionValues($expectOptv, $options);
        $this->assertSame($expectArgv, $arguments);
    }

    public function testMultipleWithAlias() : void
    {
        $options = [
            'foo' => new Option('-f,--foo', mode: Option::VALUE_OPTIONAL, multiple: true)
        ];

        $input = ['-f', '--foo', '-f', 'baz', '-f', 'dib', '--foo'];
        $arguments = $this->parse($options, $input);
        $expect = [
            'foo' => [true, true, 'baz', 'dib', true],
        ];
        $this->assertOptionValues($expect, $options);
    }

    public function testMultipleIntValueRejected() : void
    {
        $options = [
            'foo' => new Option('-f,--foo', multiple: true, type: 'int')
        ];

        $input = ['-f', '--foo', '-f', 'baz', '-f', 'dib', '--foo'];
        $arguments = $this->parse($options, $input);
        $expect = [
            'foo' => 5,
        ];
        $this->assertOptionValues($expect, $options);
    }
}
