<?php
declare(strict_types=1);

namespace AutoShell;

use AutoShell\Fake\Command\FooBar\BazOptions;

class ShellTest extends \PHPUnit\Framework\TestCase
{
    protected Shell $shell;

    protected function setUp() : void
    {
        $this->shell = Shell::new(
            'AutoShell\\Fake\\Command',
            __DIR__ . '/Fake/Command'
        );
    }

    public function testBasic() : void
    {
        $argv = ['foo-bar:baz', '1', 'a', 'b', 'c'];
        $exec = ($this->shell)($argv);

        $this->assertSame(Fake\Command\FooBar\Baz::class, $exec->class);
        $this->assertSame('__invoke', $exec->method);
        $this->assertInstanceOf(BazOptions::class, $exec->options);
        $this->assertSame([1, 'a', 'b', 'c'], $exec->arguments);
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
            [
                'foo-bar:baz',
                Fake\Command\FooBar\Baz::class,
                '__invoke'
            ],
            $exec->arguments
        );
    }
}
