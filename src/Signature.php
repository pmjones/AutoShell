<?php
declare(strict_types=1);

namespace AutoShell;

class Signature
{
    public function __construct(
        public readonly ?int $optionsPosition,
        public readonly string $optionsClass,
        public readonly array $optionAttributes,
        public readonly array $argumentParameters,
    ) {
    }
}
