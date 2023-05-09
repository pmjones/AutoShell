<?php
declare(strict_types=1);

namespace AutoShell;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class Roster
{
    public function __construct(
        protected Config $config,
        protected Reflector $reflector = new Reflector(),
        protected Format $format = new Format(),
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function __invoke() : array
    {
        $roster = [];
        $subclasses = $this->getSubclasses($this->config->directory);

        foreach ($subclasses as $subclass) {
            /** @var string $commandName */
            $commandName = preg_replace('/([a-z])([A-Z])/', '$1-$2', $subclass);
            $commandName = str_replace('\\', ':', $commandName);
            $commandName = strtolower($commandName);

            /** @var class-string $class */
            $class = $this->config->namespace . $subclass;

            if (! $this->reflector->isCommandClass($class)) {
                continue;
            }

            $rc = $this->reflector->getClass($class);
            $help = $this->reflector->getHelpAttribute($rc);
            $helpLine = '';

            if ($help !== null) {
                $helpLine = $this->format->markup($help->line);
            }

            $roster[$commandName] = $helpLine;
        }

        return $roster;
    }

    /**
     * @return array<int, string>
     */
    protected function getSubclasses(string $directory) : array
    {
        $subclasses = [];

        if (! is_dir($directory)) {
            return [];
        }

        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                 new RecursiveDirectoryIterator(
                    $directory
                )
            ),
            '/^.*\.php$/', // add $suffix
            RecursiveRegexIterator::GET_MATCH
        );

        $len = strlen($directory);

        /** @var array<int, string> $file */
        foreach ($files as $file) {
            $subclass = substr($file[0], $len, -4);
            $subclasses[] = str_replace(DIRECTORY_SEPARATOR, '\\', $subclass);
        }

        return $subclasses;
    }
}
