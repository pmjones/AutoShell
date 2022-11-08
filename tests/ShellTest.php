<?php
namespace AutoShell;

class ShellTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $this->shell = Shell::new(
            Fake\Command::CLASS,
            __DIR__ . '/Fake/Command'
        );
    }

    public function testBasic()
    {
        $argv = ['foo-bar:baz', '1', 'a', 'b', 'c'];
        $exec = ($this->shell)($argv);

        $this->assertSame(Fake\Command\FooBar\Baz::CLASS, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertInstanceOf(Options::CLASS, $exec->options);
        $this->assertSame([1, 'a', 'b', 'c'], $exec->arguments);
        $this->assertNull($exec->error);
        $this->assertNull($exec->exception);
    }

    public function testMissingArgument()
    {
        $argv = ['foo-bar:baz'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Fake\Command\FooBar\Baz::CLASS, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame(Exception\ArgumentRequired::CLASS, $exec->error);
        $this->assertInstanceof(Exception\ArgumentRequired::CLASS, $exec->exception);
    }

    public function testClassNotFound()
    {
        $argv = ['nonesuch'];
        $exec = ($this->shell)($argv);
        $this->assertNull($exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame(Exception\ClassNotFound::CLASS, $exec->error);
        $this->assertInstanceof(Exception\ClassNotFound::CLASS, $exec->exception);
    }

    public function testHelp()
    {
        $argv = [];
        $exec = ($this->shell)($argv);

        $this->assertSame(Help\RosterCommand::CLASS, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame([], $exec->arguments);

        $argv = ['help'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Help\RosterCommand::CLASS, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame([], $exec->arguments);

        $argv = ['help', 'foo-bar:baz'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Help\ManualCommand::CLASS, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame(
            [
                'foo-bar:baz',
                Fake\Command\FooBar\Baz::CLASS,
                '__invoke'
            ],
            $exec->arguments
        );
    }
}
