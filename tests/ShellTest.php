<?php
declare(strict_types=1);

namespace AutoShell;

use AutoShell\Fake\Command\FooBar\BazOptions;
use AutoShell\Fake\Command\FooBar\Dib;
use AutoShell\Fake\Command\FooBar\DibOptions;

class ShellTest extends \PHPUnit\Framework\TestCase
{
    protected Shell $shell;

    protected function setUp() : void
    {
        $this->shell = Shell::new(
            'AutoShell\\Fake\\Command',
            __DIR__ . '/Fake/Command',
        );
    }

    public function testBasic() : void
    {
        $argv = ['foo-bar:baz', '1', 'a', 'b', 'c'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Fake\Command\FooBar\Baz::class, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertInstanceOf(BazOptions::class, $exec->arguments[0]);
        $actual = $exec->arguments;
        array_shift($actual);
        $this->assertSame([1, 'a', 'b', 'c'], $actual);
        $this->assertNull($exec->error);
        $this->assertNull($exec->exception);
    }

    public function testMissingArgument() : void
    {
        $argv = ['foo-bar:baz'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Fake\Command\FooBar\Baz::class, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame(Exception\ArgumentRequired::class, $exec->error);
        $this->assertInstanceof(Exception\ArgumentRequired::class, $exec->exception);
    }

    public function testClassNotFound() : void
    {
        $argv = ['nonesuch'];
        $exec = ($this->shell)($argv);
        $this->assertNull($exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame(Exception\ClassNotFound::class, $exec->error);
        $this->assertInstanceof(Exception\ClassNotFound::class, $exec->exception);
    }

    public function testHelp() : void
    {
        $argv = [];
        $exec = ($this->shell)($argv);
        $this->assertSame(Help\RosterCommand::class, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame([], $exec->arguments);
        $argv = ['help'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Help\RosterCommand::class, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame([], $exec->arguments);
        $argv = ['help', 'foo-bar:baz'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Help\ManualCommand::class, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertSame(
            ['foo-bar:baz', Fake\Command\FooBar\Baz::class, '__invoke'],
            $exec->arguments,
        );
    }

    public function testOptions() : void
    {
        $argv = ['foo-bar:dib', '88', '-a', '-b', 'bval', '--charlie'];
        $exec = ($this->shell)($argv);
        $this->assertSame(Dib::class, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertInstanceOf(DibOptions::class, $exec->arguments[0]);
        $this->assertSame(true, $exec->arguments[0]->alpha);
        $this->assertSame('bval', $exec->arguments[0]->bravo);
        $this->assertSame('delta', $exec->arguments[0]->charlie);
        $this->assertSame(88, $exec->arguments[1]);
        $this->assertSame('kay', $exec->arguments[2]);
        $this->assertNull($exec->error);
        $this->assertNull($exec->exception);
    }
}
