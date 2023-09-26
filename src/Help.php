<?php
declare(strict_types=1);

namespace AutoShell;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class Help
{
    public function __construct(
        public readonly string $line,
        public readonly ?string $body = null,
    ) {
    }
}
