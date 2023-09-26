<?php
declare(strict_types=1);

namespace AutoShell;

class FormatTest extends \PHPUnit\Framework\TestCase
{
    public function testMarkup() : void
    {
        $format = new Format();
        $text = <<<TEXT
        *DESCRIPTION*

            Use _underline_ as well. Make sure that
            markup *spans multiple words*. _Span
            multiple lines_ if needed. But ignore
            \*markup* that is back\slashed.

        TEXT;
        $actual = $format->markup($text);
        $expect = <<<TEXT
        [1mDESCRIPTION[0m

            Use [4munderline[0m as well. Make sure that
            markup [1mspans multiple words[0m. [4mSpan
            multiple lines[0m if needed. But ignore
            *markup* that is backslashed.
        [0m
        TEXT;
        $this->assertSame($expect, $actual);
    }
}
