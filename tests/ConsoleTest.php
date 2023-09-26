<?php
declare(strict_types=1);

namespace AutoShell;

class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    protected Stdmem $stdout;

    protected Stdmem $stderr;

    protected Console $console;

    protected Format $format;

    protected function osdir(string $dir) : string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $dir);
    }

    protected function setUp() : void
    {
        $this->stdout = new Stdmem();
        $this->stderr = new Stdmem();

        $this->console = Console::new(
            namespace: 'AutoShell\\Fake\\Command',
            directory: $this->osdir(__DIR__ . '/Fake/Command'),
            stdout: $this->stdout,
            stderr: $this->stderr,
            help: "AutoShell fake test command." . PHP_EOL . PHP_EOL,
        );

        $this->format = new Format();
    }

    protected function assertStdout(string $expect) : void
    {
        $expect = str_replace("\r\n", "\n", (string) $expect);
        $actual = str_replace("\r\n", "\n", (string) $this->stdout);
        $this->assertSame($expect, $actual);
    }

    protected function assertStderr(string $expect) : void
    {
        $expect = str_replace("\r\n", "\n", (string) $expect);
        $actual = str_replace("\r\n", "\n", (string) $this->stderr);
        $this->assertSame($expect, $actual);
    }

    public function testHelpRoster() : void
    {
        $exit = ($this->console)(['console.php', 'help']);
        $this->assertSame(0, $exit);
        $expect = <<<TEXT
AutoShell fake test command.

foo-bar:baz
    No help available.

foo-bar:dib
    Dibs an i, with optional alpha, bravo, and charlie behaviors.

foo-bar:gir
    Command for Gir.

foo-bar:qux
    Command for qux operations.


TEXT;
        $this->assertStdout($expect);
        $this->assertStderr('');
    }

    public function testHelpManual() : void
    {
        $exit = ($this->console)(['console.php', 'help', 'foo-bar:qux']);
        $this->assertSame(0, $exit);
        $expect = <<<TEXT
AutoShell fake test command.

NAME
    foo-bar:qux -- Command for qux operations.

SYNOPSIS
    foo-bar:qux

CLASS METHOD
    AutoShell\Fake\Command\FooBar\Qux::__invoke()

TEXT;
        $this->assertStdout($expect);
        $this->assertStderr('');
    }

    public function testSuccess() : void
    {
        $exit = ($this->console)(['console.php', 'foo-bar:qux']);
        $this->assertSame(0, $exit);
        $this->assertStdout('');
        $this->assertStderr('');
    }

    public function testFailure() : void
    {
        $exit = ($this->console)(['console.php', 'foo-bar:qux', '--no-such-option']);
        $this->assertSame(1, $exit);
        $this->assertStdout('');
        $this->assertStderr('Option --no-such-option is not defined.' . PHP_EOL);
    }

    public function testNoCommands() : void
    {
        $directory = $this->osdir('/No-Such-Dir/');
        $this->console = Console::new(
            namespace: 'AutoShell\\Fake\\Command',
            directory: $directory,
            stdout: $this->stdout,
            stderr: $this->stderr,
        );

        $exit = ($this->console)(['console.php', 'help']);
        $this->assertSame(0, $exit);
        $expect = <<<TEXT
        No commands found.
        Namespace: AutoShell\Fake\Command\
        Directory: {$directory}

        TEXT;
        $this->assertStdout($expect);
        $this->assertStderr('');
    }
}
