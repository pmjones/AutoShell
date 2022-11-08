<?php
declare(strict_types=1);

namespace AutoShell;

/**
 * Control codes from <http://domoticx.com/terminal-codes-ansivt100/>.
 */
class Format
{
    public const RESET = "\x1B[0m";

    public const BOLD = "\x1B[1m";

    public const DIM = "\x1B[2m";

    public const UL = "\x1B[4m";

    public function bold(string $str) : string
    {
        return $this->style(static::BOLD, $str);
    }

    public function ul(string $str) : string
    {
        return $this->style(static::UL, $str);
    }

    public function dim(string $str) : string
    {
        return $this->style(static::DIM, $str);
    }

    public function strip(string $str) : string
    {
        return strtr($str, [
            static::RESET => '',
            static::BOLD => '',
            static::DIM => '',
            static::UL => '',
        ]);
    }

    public function markup(string $str) : string
    {
        $markup = [
            '/(^|\W)((?<!(\\\\))\*)(.*?)(\*)(\W|$)/ms' => '$1' . static::BOLD . '$4' . static::RESET . '$6',
            '/(^|\W)(\_)(.*?)(\_)(\W|$)/ms' => '$1' . static::UL   . '$3' . static::RESET . '$5',
        ];

        foreach ($markup as $find => $replace) {
            $str = preg_replace($find, $replace, $str);
        }

        return strtr($str, ['\\' => '']) . static::RESET;
    }

    protected function style(string $code, string $str) : string
    {
        return $code . $str . static::RESET;
    }
}
