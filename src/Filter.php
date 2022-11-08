<?php
declare(strict_types=1);

namespace AutoShell;

use ReflectionParameter;

class Filter
{
    public function __invoke(mixed $value, ?string $type, string $errmsg) : mixed
    {
        if ($type === null) {
            return $value;
        }

        $method = 'to' . ucfirst($type);
        return $this->$method($value, $errmsg);
    }

    /**
     * @return array<int, mixed>
     */
    protected function toArray(mixed $value, string $errmsg) : array
    {
        if (is_array($value)) {
            return $value;
        }

        return str_getcsv($value); // @phpstan-ignore-line
    }

    protected function toBool(mixed $value, string $errmsg) : bool
    {
        if (is_bool($value)) {
            return $value;
        }

        /** @var string */
        $lower = strtolower($value); // @phpstan-ignore-line

        if (in_array($lower, ['1', 't', 'true', 'y', 'yes'])) {
            return true;
        }

        if (in_array($lower, ['0', 'f', 'false', 'n', 'no'])) {
            return false;
        }

        throw $this->argumentInvalid($value, $errmsg);
    }

    protected function toFloat(mixed $value, string $errmsg) : float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        throw $this->argumentInvalid($value, $errmsg);
    }

    protected function toInt(mixed $value, string $errmsg) : int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value) && (int) $value == $value) {
            return (int) $value;
        }

        throw $this->argumentInvalid($value, $errmsg);
    }

    protected function toMixed(mixed $value, string $errmsg) : mixed
    {
        return $value;
    }

    protected function toString(mixed $value, string $errmsg) : string
    {
        return (string) $value; // @phpstan-ignore-line
    }

    protected function argumentInvalid(
        mixed $value,
        string $errmsg,
    ) : Exception\ArgumentInvalid
    {
        $value = var_export($value, true);
        return new Exception\ArgumentInvalid($errmsg . ", actually {$value}");
    }
}
