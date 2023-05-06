<?php
declare(strict_types=1);

namespace AutoShell;

class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    protected string $stdout = '';

    protected string $stderr = '';

    protected Console $console;

    protected Format $format;

    protected function setUp() : void
    {
        $this->console = Console::new(
            namespace: 'AutoShell\\Fake\\Command',
            directory: __DIR__ . '/Fake/Command',
            stdout: fn (string $output) => $this->stdout .= $output,
            stderr: fn (string $output) => $this->stderr .= $output,
            header: "AutoShell fake test command." . PHP_EOL . PHP_EOL,
        );

        $this->format = new Format();
    }

    protected function tearDown() : void
    {
        $this->stdout = '';
        $this->stderr = '';
    }

    protected function assertStdout(string $expect) : void
    {
        $this->assertSame(
            $expect,
            $this->format->strip($this->stdout)
        );
    }

    protected function assertStderr(string $expect) : void
    {
        $this->assertSame(
            $expect,
            $this->format->strip($this->stderr)
        );
    }

    public function testHelpRoster() : void
    {
        $exit = ($this->console)(['run.php', 'help']);
        $this->assertSame(0, $exit);
        $expect = <<<TEXT
AutoShell fake test command.

foo-bar:baz
    No help available.

foo-bar:dib
    Dibs an i, with optional alpha, bravo, and charlie behaviors.

foo-bar:qux
    Command for qux operations.


TEXT;
        $this->assertStdout($expect);
        $this->assertStderr('');
    }

    public function testHelpManual() : void
    {
        $exit = ($this->console)(['run.php', 'help', 'foo-bar:qux']);
        $this->assertSame(0, $exit);
        $expect = <<<TEXT
AutoShell fake test command.

NAME
    foo-bar:qux -- Command for qux operations.

SYNOPSIS
    foo-bar:qux


TEXT;
        $this->assertStdout($expect);
        $this->assertStderr('');
    }

    public function testSuccess() : void
    {
        $exit = ($this->console)(['run.php', 'foo-bar:qux']);
        // $this->assertSame(0, $exit);
        $this->assertStdout('');
        $this->assertStderr('');
    }

    public function testFailure() : void
    {
        $exit = ($this->console)(['run.php', 'foo-bar:qux', '--no-such-option']);
        $this->assertSame(1, $exit);
        $this->assertStdout('');
        $this->assertStderr('Option --no-such-option is not defined.' . PHP_EOL);
    }
}
