<?php
declare(strict_types=1);

namespace AutoShell;

class RosterTest extends \PHPUnit\Framework\TestCase
{
    protected Config $config;

    protected Roster $roster;

    protected function setUp() : void
    {
        $this->config = new Config(
            namespace: Fake\Command::CLASS,
            directory: __DIR__ . '/Fake/Command',
        );

        $this->roster = new Roster($this->config);
    }

    public function test()
    {
        $actual = ($this->roster)();
        $expect = [
            "foo-bar:baz" => "",
            "foo-bar:dib" => "Dibs an i, with optional alpha, bravo, and charlie behaviors.",
            "foo-bar:qux" => "Command for qux operations.",
        ];
        $this->assertSame($expect, $actual);
    }
}
