<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;
use ReflectionMethod;

class Manual
{
    public function __construct(
        protected Reflector $reflector = new Reflector(),
        protected Format $format = new Format()
    ) {
    }

    /**
     * @param class-string $class
     */
    public function __invoke(
        string $commandName,
        string $class,
        string $method
    ) : string
    {
        $rc = $this->reflector->getClass($class);

        $name = $this->name($rc);

        if ($name) {
            $name = " -- {$name}";
        }

        $synopsis = $this->format->bold($commandName);

        $options = $this->options($rc);

        if ($options) {
            $synopsis .= " [options]";
        }

        $rm = $this->reflector->getMethod($rc, $method);
        $argumentSynopsis = $this->argumentSynopsis($rm);

        if ($argumentSynopsis) {
            $synopsis .= " [--] {$argumentSynopsis}";
        }

        $out = [];
        $out[] = $this->format->bold("NAME");
        $out[] = "    " . $this->format->bold($commandName) . $name;
        $out[] = "";
        $out[] = $this->format->bold("SYNOPSIS");
        $out[] = "    {$synopsis}";

        $arguments = $this->arguments($rm);

        if ($arguments !== null) {
            $out[] = "";
            $out[] = $arguments;
        }

        $out[] = $options;
        $body = $this->body($rc);

        if ($body !== null) {
            $out[] = $this->format->markup($body);
        }

        return implode(PHP_EOL, $out) . PHP_EOL;
    }

    protected function name(ReflectionClass $rc) : ?string
    {
        $help = $this->reflector->getHelpAttribute($rc);
        return ($help === null) ? null : $help->line;
    }

    protected function body(ReflectionClass $rc) : ?string
    {
        $help = $this->reflector->getHelpAttribute($rc);
        return ($help === null) ? null : $help->body;
    }

    protected function argumentSynopsis(ReflectionMethod $rm) : string
    {
        $rps = $this->reflector->getArgumentParameters($rm);

        $arguments = [];

        foreach ($rps as $rp) {
            $name = $this->format->ul($rp->getName());
            $name = $rp->isOptional() ? "[{$name}]" : $name;

            if ($rp->isVariadic()) {
                $name .= ' ...';
            }

            $arguments[] = $name;
        }

        return implode(' ', $arguments);
    }

    protected function options(ReflectionClass $rc) : string
    {
        $out = [];

        $options = $this->reflector->getOptionAttributes($rc);

        if (count($options) === 0) {
            return '';
        }

        $out[] = $this->format->bold("OPTIONS");

        foreach ($options as $option) {

            $default = '';

            if ($option->default !== null) {
                $default = ' ' . $this->format->dim('(default: ' . var_export($option->default, true) . ')');
            }

            foreach ($option->names as $name) {
                $out[] = "    " . $this->option($option, $name) . $default;
            }

            $out[] = "        " . ($option->help ?? "No help available.");
            $out[] = "";
        }

        return implode(PHP_EOL, $out);
    }

    protected function option(Option $option, string $name) : string
    {
        $short = strlen($name) === 1;
        $str = $this->format->bold(($short ? '-' : '--') . $name);
        $argname = $this->format->ul($option->argname ?? 'value');

        switch ($option->argument) {
            case Option::VALUE_REQUIRED:
                $str .= ($short ? ' ' : '=') . $argname;
                break;
            case Option::VALUE_OPTIONAL:
                $str .= ($short ? ' [' : '[=') . "{$argname}]";
                break;
        }

        return $str;
    }

    protected function arguments(ReflectionMethod $rm) : ?string
    {
        $out = [];

        $rps = $this->reflector->getArgumentParameters($rm);

        if (count($rps) === 0) {
            return null;
        }

        $out[] = $this->format->bold("ARGUMENTS");

        foreach ($rps as $rp) {

            $tmp = $this->format->ul($rp->getName());

            if ($rp->isDefaultValueAvailable()) {
                $tmp = "{$tmp} " . $this->format->dim(
                    '(default: ' . var_export($rp->getDefaultValue(), true) . ')'
                );
            }

            $out[] = "    " . $tmp;

            $help = $this->reflector->getHelpAttribute($rp);

            if ($help !== null) {
                $out[] = "        " . ($help->line);
            } else {
                $out[] = "         No help available.";
            }

            $out[] = "";
        }

        return implode(PHP_EOL, $out);
    }
}
