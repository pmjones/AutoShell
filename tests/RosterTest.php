<?php
declare(strict_types=1);

namespace AutoShell;

class RosterTest extends \PHPUnit\Framework\TestCase
{
    protected Config $config;

    protected Roster $roster;

    protected Format $format;

    protected function setUp() : void
    {
        $this->config = new Config(
            namespace: 'AutoShell\\Fake\\Command',
            directory: __DIR__ . '/Fake/Command',
        );
        $this->format = new Format();
        $this->roster = new Roster($this->config, new Reflector(), $this->format);
    }

    public function test() : void
    {
        $actual = ($this->roster)();

        foreach ($actual as $key => $val) {
            $actual[$key] = $this->format->strip($val);
        }

        $expect = [
            "foo-bar:baz" => "",
            "foo-bar:dib" => "Dibs an i, with optional alpha, bravo, and charlie behaviors.",
            "foo-bar:gir" => "Command for Gir.",
            "foo-bar:qux" => "Command for qux operations.",
        ];
        $this->assertSame($expect, $actual);
    }
}
