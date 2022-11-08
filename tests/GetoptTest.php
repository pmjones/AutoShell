<?php
namespace AutoShell;

class GetoptTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $this->getopt = new Getopt(new Filter());
    }

    protected function assertSameValues(array $expect, OptionCollection $optionCollection)
    {
        $actual = [];

        foreach ($optionCollection as $option) {
            foreach ($option->names as $name) {
                $actual[$name] = $option->getValue();
            }
        }

        $this->assertSame($expect, $actual);
    }

    public function testParse_noOptions()
    {
        $optionCollection = new OptionCollection();
        $input = ['abc', 'def'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $this->assertCount(0, $optionCollection);
        $expect = ['abc', 'def'];
        $this->assertSame($expect, $input);
    }

    public function testParse_undefinedOption()
    {
        $optionCollection = new OptionCollection();
        $input = ['-z', 'def'];
        $this->expectException(Exception\OptionNotDefined::CLASS);
        $this->expectExceptionMessage("-z is not defined.");
        $this->getopt->parse($optionCollection, $input);
    }

    public function testParse_longRejected()
    {
        $optionCollection = new OptionCollection([
            new Option('foo-bar'),
        ]);
        $input = ['--foo-bar'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['foo-bar' => true];
        $this->assertSameValues($expect, $optionCollection);

        $optionCollection = new OptionCollection([
            new Option('foo-bar'),
        ]);
        $input = ['--foo-bar=baz'];
        $this->expectException(Exception\ArgumentRejected::CLASS);
        $this->expectExceptionMessage("--foo-bar does not accept an argument.");
        $this->getopt->parse($optionCollection, $input);
    }

    public function testParse_longRequired()
    {
        // '=' as separator
        $optionCollection = new OptionCollection([
            new Option('foo-bar', argument: Option::VALUE_REQUIRED)
        ]);
        $input = ['--foo-bar=baz'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['foo-bar' => 'baz'];
        $this->assertSameValues($expect, $optionCollection);

        // ' ' as separator
        $optionCollection = new OptionCollection([
            new Option('foo-bar', argument: Option::VALUE_REQUIRED)
        ]);
        $input = ['--foo-bar', 'baz'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $this->assertSameValues($expect, $optionCollection);

        // missing required value
        $optionCollection = new OptionCollection([
            new Option('foo-bar', argument: Option::VALUE_REQUIRED)
        ]);
        $input = ['--foo-bar'];
        $this->expectException(Exception\ArgumentRequired::CLASS);
        $this->expectExceptionMessage("--foo-bar requires an argument.");
        $this->getopt->parse($optionCollection, $input);
    }

    public function testParse_longOptional()
    {
        $optionCollection = new OptionCollection([
            new Option('foo-bar', argument: Option::VALUE_OPTIONAL)
        ]);
        $input = ['--foo-bar'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['foo-bar' => true];
        $this->assertSameValues($expect, $optionCollection);

        $optionCollection = new OptionCollection([
            new Option('foo-bar', argument: Option::VALUE_OPTIONAL)
        ]);
        $input = ['--foo-bar=baz'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['foo-bar' => 'baz'];
        $this->assertSameValues($expect, $optionCollection);
    }

    public function testParse_longMultiple()
    {
        $optionCollection = new OptionCollection([
            new Option('foo-bar', argument: Option::VALUE_OPTIONAL, multiple: true)
        ]);

        $input = [
            '--foo-bar',
            '--foo-bar',
            '--foo-bar=baz',
            '--foo-bar=dib',
            '--foo-bar'
        ];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['foo-bar' => [true, true, 'baz', 'dib', true]];
        $this->assertSameValues($expect, $optionCollection);
    }

    public function testParse_shortRejected()
    {
        $optionCollection = new OptionCollection([
            new Option('f')
        ]);
        $input = ['-f'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['f' => true];
        $this->assertSameValues($expect, $optionCollection);

        $optionCollection = new OptionCollection([
            new Option('f')
        ]);
        $input = ['-f', 'baz'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['f' => true];
        $this->assertSameValues($expect, $optionCollection);
        $this->assertSame(['baz'], $arguments);
    }

    public function testParse_shortRequired()
    {
        $optionCollection = new OptionCollection([
            new Option('f', argument: Option::VALUE_REQUIRED)
        ]);
        $input = ['-f', 'baz'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['f' => 'baz'];
        $this->assertSameValues($expect, $optionCollection);

        $optionCollection = new OptionCollection([
            new Option('f', argument: Option::VALUE_REQUIRED)
        ]);
        $input = ['-f'];
        $this->expectException(Exception\ArgumentRequired::CLASS);
        $this->expectExceptionMessage("-f requires an argument.");
        $this->getopt->parse($optionCollection, $input);
    }

    public function testParse_shortOptional()
    {
        $optionCollection = new OptionCollection([
            new Option('f', argument: Option::VALUE_OPTIONAL)
        ]);
        $input = ['-f'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['f' => true];
        $this->assertSameValues($expect, $optionCollection);

        $optionCollection = new OptionCollection([
            new Option('f', argument: Option::VALUE_OPTIONAL)
        ]);
        $input = ['-f', 'baz'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['f' => 'baz'];
        $this->assertSameValues($expect, $optionCollection);
    }

    public function testParse_shortMultiple()
    {
        $optionCollection = new OptionCollection([
            new Option('f', argument: Option::VALUE_OPTIONAL, multiple: true)
        ]);

        $input = ['-f', '-f', '-f', 'baz', '-f', 'dib', '-f'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = ['f' => [true, true, 'baz', 'dib', true]];
        $this->assertSameValues($expect, $optionCollection);
    }

    public function testParse_shortCluster()
    {
        $optionCollection = new OptionCollection([
            new Option('f'),
            new Option('b'),
            new Option('z'),
        ]);

        $input = ['-fbz'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = [
            'f' => true,
            'b' => true,
            'z' => true,
        ];
        $this->assertSameValues($expect, $optionCollection);
    }

    public function testParse_shortClusterRequired()
    {
        $optionCollection = new OptionCollection([
            new Option('f'),
            new Option('b', argument: Option::VALUE_REQUIRED),
            new Option('z'),
        ]);

        $input = ['-fbz'];
        $this->expectException(Exception\ArgumentRequired::CLASS);
        $this->expectExceptionMessage("-b requires an argument.");
        $this->getopt->parse($optionCollection, $input);
    }

    public function testParseAndGet()
    {
        $optionCollection = new OptionCollection([
            new Option('foo-bar', argument: Option::VALUE_REQUIRED),
            new Option('b'),
            new Option('z', argument: Option::VALUE_OPTIONAL),
        ]);

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

        $arguments = $this->getopt->parse($optionCollection, $input);

        $expectOptv = [
            'foo-bar' => 'zim',
            'b' => true,
            'z' => 'qux',
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

        $this->assertSameValues($expectOptv, $optionCollection);
        $this->assertSame($expectArgv, $arguments);
    }

    public function testMultipleWithAlias()
    {
        $optionCollection = new OptionCollection([
            new Option('-f,--foo', argument: Option::VALUE_OPTIONAL, multiple: true)
        ]);

        $input = ['-f', '-f', '-f', 'baz', '-f', 'dib', '-f'];
        $arguments = $this->getopt->parse($optionCollection, $input);
        $expect = [
            'f' => [true, true, 'baz', 'dib', true],
            'foo' => [true, true, 'baz', 'dib', true],
        ];
        $this->assertSameValues($expect, $optionCollection);
    }
}
