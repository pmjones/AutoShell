<?php
declare(strict_types=1);

namespace AutoShell;

use Stringable;

class Config
{
    public readonly string $namespace;

    public readonly string $directory;

    public function __construct(
        string $namespace,
        string $directory,
        public readonly string $method = '__invoke',
        public readonly string $suffix = '',
        public readonly string|Stringable $help = '',
    ) {
        $this->namespace = rtrim($namespace, '\\') . '\\';
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
