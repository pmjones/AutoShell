<?php
declare(strict_types=1);

namespace AutoShell;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
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
        public readonly string $mode = self::VALUE_REJECTED,
        public readonly mixed $default = null,
        public readonly bool $multiple = false,
        public readonly ?string $help = null,
        public readonly ?string $type = null,
        public readonly string $valname = '',
    ) {
        $names = explode(',', $names);

        foreach ($names as &$name) {
            $name = trim($name, '- ');
        }

        sort($names);
        $this->names = $names;
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
        if ($this->mode === Option::VALUE_REJECTED) {
            $this->setValue($this->default ?? true, $filter);
            return;
        }

        $value = reset($input);
        $capture = ! empty($value) && substr($value, 0, 1) !== '-';

        if ($capture) {
            $this->setValue($value, $filter);
            array_shift($input);
            return;
        }

        if ($this->mode === Option::VALUE_OPTIONAL) {
            $this->setValue($this->default ?? true, $filter);
            return;
        }

        throw new Exception\ArgumentRequired(
            "{$this->names()} requires a value."
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

        if ($this->mode === self::VALUE_REJECTED) {
            $this->equalsRejected($value, $filter);
            return;
        }

        if ($this->mode === self::VALUE_OPTIONAL) {
            $this->equalsOptional($value, $filter);
            return;
        }

        $this->equalsRequired($value, $filter);
    }

    protected function equalsRejected(string $value, Filter $filter) : void
    {
        if ($value === '') {
            $this->setValue($this->default ?? true, $filter);
            return;
        }

        throw new Exception\ArgumentRejected(
            "{$this->names()} does not accept a value."
        );
    }

    protected function equalsRequired(string $value, Filter $filter) : void
    {
        if ($value !== '') {
            $this->setValue($value, $filter);
            return;
        }

        throw new Exception\ArgumentRequired(
            "{$this->names()} requires a value."
        );
    }

    protected function equalsOptional(string $value, Filter $filter) : void
    {
        $default = $this->default ?? true;
        $value === '' ? $default : $value;
        $this->setValue($value, $filter);
    }

    protected function setValue(mixed $value, Filter $filter) : void
    {
        if ($this->mode === static::VALUE_REJECTED && $this->type === 'int') {
            $this->value = ($this->value === null) ? 1 : $this->value + 1;
            return;
        }

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
