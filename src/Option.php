<?php
declare(strict_types=1);

namespace AutoShell;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Option
{
    public const VALUE_REQUIRED = 'VALUE_REQUIRED';

    public const VALUE_OPTIONAL = 'VALUE_OPTIONAL';

    public const VALUE_REJECTED = 'VALUE_REJECTED';

    /**
     * @var array<int, string>
     */
    public readonly array $names;

    protected mixed $value = null;

    public function __construct(
        string $names,
        public readonly string $argument = self::VALUE_REJECTED,
        public readonly bool $multiple = false,
        public readonly ?string $type = null,
        public readonly mixed $default = null,
        public readonly ?string $help = null,
        public readonly ?string $argname = null,
    ) {
        $names = explode(',', $names);

        foreach ($names as &$name) {
            $name = trim($name, '- ');
        }

        sort($names);

        $this->names = $names;

        if (
            $this->default !== null
            && $this->argument !== static::VALUE_OPTIONAL
        ) {
            throw new Exception\DefaultNotAllowed();
        }
    }

    public function getValue() : mixed
    {
        return $this->value;
    }

    /**
     * @param array<int, string> &$input
     */
    public function capture(array &$input, Filter $filter) : void
    {
        if ($this->argument === Option::VALUE_REJECTED) {
            $this->setValue(true, $filter);
            return;
        }

        $value = reset($input);
        $capture = ! empty($value) && substr($value, 0, 1) !== '-';

        if ($capture) {
            $this->setValue($value, $filter);
            array_shift($input);
            return;
        }

        if ($this->argument !== Option::VALUE_REQUIRED) {
            $this->setValue(true, $filter);
            return;
        }

        throw new Exception\ArgumentRequired(
            "{$this->names()} requires an argument."
        );
    }

    public function names() : string
    {
        $dashed = [];

        foreach ($this->names as $name) {
            $dashed[] = strlen($name) === 1 ? "-{$name}" : "--{$name}";
        }

        return implode(',', $dashed);
    }

    public function equals(string $value, Filter $filter) : void
    {
        $value = trim($value);

        if ($this->argument === self::VALUE_REJECTED) {
            $this->equalsRejected($value, $filter);
            return;
        }

        if ($this->argument === self::VALUE_REQUIRED) {
            $this->equalsRequired($value, $filter);
        }

        $value === '' ? true : $value;
        $this->setValue($value, $filter);
    }

    protected function equalsRejected(string $value, Filter $filter) : void
    {
        if ($value === '') {
            $this->setValue(true, $filter);
            return;
        }

        throw new Exception\ArgumentRejected(
            "{$this->names()} does not accept an argument."
        );
    }

    protected function equalsRequired(string $value, Filter $filter) : void
    {
        if ($value !== '') {
            $this->setValue($value, $filter);
            return;
        }

        throw new Exception\ArgumentRequired(
            "{$this->names()} requires an argument."
        );
    }

    protected function setValue(mixed $value, Filter $filter) : void
    {
        $errmsg = "Option {$this->names()} expected {$this->type} value";
        $value = $filter($value, $this->type, $errmsg);

        if (! $this->multiple) {
            $this->value = $value;
            return;
        }

        if ($this->value === null) {
            $this->value = [];
        }

        $this->value[] = $value; // @phpstan-ignore-line
    }
}
