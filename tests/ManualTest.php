<?php
declare(strict_types=1);

namespace AutoShell;

class ManualTest extends \PHPUnit\Framework\TestCase
{
    protected Format $format;

    protected Manual $manual;

    protected function setUp() : void
    {
        $this->format = new Format();
        $this->manual = new Manual(new Reflector(), $this->format);
    }

    protected function assertOutput(string $expect, string $actual) : void
    {
        $actual = str_replace("\r\n", "\n", $this->format->strip($actual));
        $this->assertSame($expect, $actual);
    }

    public function testBasic() : void
    {
        $actual = $this->manual
            ->__invoke('foo-bar:dib', Fake\Command\FooBar\Dib::class, '__invoke');
        $expect = <<<TEXT
        NAME
            foo-bar:dib -- Dibs an i, with optional alpha, bravo, and charlie behaviors.

        SYNOPSIS
            foo-bar:dib [options] [--] i [k]

        ARGUMENTS
            i
                The i to be dibbed

            k (default: 'kay')
                 No help available.

        OPTIONS
            -a
            --alpha
                The alpha option.

            -b bravo
            --bravo=bravo
                No help available.

            -c [charlie] (default: 'delta')
            --charlie[=charlie] (default: 'delta')
                No help available.

        DESCRIPTION
            This is a description of the command.

            There are quite a few nuances.

        EXAMPLES
            Here are some examples of how to use the command.

            Please use your imagination.

        CLASS METHOD
            AutoShell\Fake\Command\FooBar\Dib::__invoke()

        TEXT;
        $this->assertOutput($expect, $actual);
    }

    public function testNoOptionsNoArguments() : void
    {
        $actual = $this->manual
            ->__invoke('foo-bar:qux', Fake\Command\FooBar\Qux::class, '__invoke');
        $expect = <<<TEXT
        NAME
            foo-bar:qux -- Command for qux operations.

        SYNOPSIS
            foo-bar:qux

        CLASS METHOD
            AutoShell\Fake\Command\FooBar\Qux::__invoke()

        TEXT;
        $this->assertOutput($expect, $actual);
    }

    public function testVariadicArguments() : void
    {
        $actual = $this->manual
            ->__invoke('foo-bar:baz', Fake\Command\FooBar\Baz::class, '__invoke');
        $expect = <<<TEXT
        NAME
            foo-bar:baz

        SYNOPSIS
            foo-bar:baz [options] [--] i [tail] ...

        ARGUMENTS
            i
                 No help available.

            tail
                 No help available.

        OPTIONS
            -z
            --zim
                No help available.

        CLASS METHOD
            AutoShell\Fake\Command\FooBar\Baz::__invoke()

        TEXT;
        $this->assertOutput($expect, $actual);
    }

    public function testMultiOptions() : void
    {
        $actual = $this->manual
            ->__invoke('foo-bar:gir', Fake\Command\FooBar\Gir::class, '__invoke');
        $expect = <<<TEXT
        NAME
            foo-bar:gir -- Command for Gir.

        SYNOPSIS
            foo-bar:gir [options] [--] doom

        ARGUMENTS
            doom
                 No help available.

        OPTIONS
            -a
            --alpha
                The alpha option.

            -b bravo
            --bravo=bravo
                No help available.

            -c [charlie] (default: 'delta')
            --charlie[=charlie] (default: 'delta')
                No help available.

            -z
            --zim
                No help available.

        CLASS METHOD
            AutoShell\Fake\Command\FooBar\Gir::__invoke()

        TEXT;
        $this->assertOutput($expect, $actual);
    }
}
