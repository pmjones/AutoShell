<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionClass;
use ReflectionMethod;

class Manual
{
    public function __construct(
        protected Reflector $reflector,
        protected Format $format,
    ) {
    }

    /**
     * @param class-string $commandClass
     */
    public function __invoke(
        string $commandName,
        string $commandClass,
        string $commandMethod
    ) : string
    {
        $signature = $this->reflector->getSignature(
            $commandClass,
            $commandMethod
        );

        $summary = $this->summary($signature);

        if ($summary) {
            $summary = " -- {$summary}";
        }

        $synopsis = $this->format->bold($commandName);

        $options = $this->options($signature);

        if ($options) {
            $synopsis .= " [options]";
        }

        $argumentSynopsis = $this->argumentSynopsis($signature);

        if ($argumentSynopsis) {
            $synopsis .= " [--] {$argumentSynopsis}";
        }

        $out = [];
        $out[] = $this->format->bold("NAME");
        $out[] = "    " . $this->format->bold($commandName) . $summary;
        $out[] = "";
        $out[] = $this->format->bold("SYNOPSIS");
        $out[] = "    {$synopsis}";

        $arguments = $this->arguments($signature);

        if ($arguments !== null) {
            $out[] = "";
            $out[] = $arguments;
        }

        $out[] = $options;
        $body = $this->body($signature);

        if ($body !== null) {
            $out[] = $this->format->markup($body);
        }

        $out[] = $this->format->bold("CLASS METHOD");
        $out[] = "    {$commandClass}::{$commandMethod}()";

        return implode(PHP_EOL, $out) . PHP_EOL;
    }

    protected function summary(Signature $signature) : ?string
    {
        $help = $signature->getCommandHelp();
        return ($help === null) ? null : $this->format->markup($help->line);
    }

    protected function body(Signature $signature) : ?string
    {
        $help = $signature->getCommandHelp();
        return ($help === null) ? null : $help->body;
    }

    protected function argumentSynopsis(Signature $signature) : string
    {
        $arguments = [];

        foreach ($signature->getArgumentParameters() as $argumentParameter) {
            $name = $this->format->ul($argumentParameter->getName());
            $name = $argumentParameter->isOptional() ? "[{$name}]" : $name;

            if ($argumentParameter->isVariadic()) {
                $name .= ' ...';
            }

            $arguments[] = $name;
        }

        return implode(' ', $arguments);
    }

    protected function options(Signature $signature) : string
    {
        $out = [];

        $optionCollection = $signature->getOptionCollection();

        if (count($optionCollection) === 0) {
            return '';
        }

        $out[] = $this->format->bold("OPTIONS");

        foreach ($optionCollection as $option) {

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
        $valname = $this->format->ul($option->valname);

        switch ($option->mode) {
            case Option::VALUE_REQUIRED:
                $str .= ($short ? ' ' : '=') . $valname;
                break;
            case Option::VALUE_OPTIONAL:
                $str .= ($short ? ' [' : '[=') . "{$valname}]";
                break;
        }

        return $str;
    }

    protected function arguments(Signature $signature) : ?string
    {
        $out = [];

        $argumentParameters = $signature->getArgumentParameters();

        if (count($argumentParameters) === 0) {
            return null;
        }

        $out[] = $this->format->bold("ARGUMENTS");

        foreach ($argumentParameters as $argumentNumber => $argumentParameter) {

            $tmp = $this->format->ul($argumentParameter->getName());

            if ($argumentParameter->isDefaultValueAvailable()) {
                $tmp = "{$tmp} " . $this->format->dim(
                    '(default: ' . var_export($argumentParameter->getDefaultValue(), true) . ')'
                );
            }

            $out[] = "    " . $tmp;

            $help = $signature->getArgumentHelp($argumentNumber);

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
