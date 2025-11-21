<?php

namespace DvSoft\AttributeChangeLog\Tests\Helpers;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class StatusDto
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(StatusDto $other): bool
    {
        return $this->value === $other->value;
    }
}

class StatusDtoCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?StatusDto
    {
        if ($value === null) {
            return null;
        }

        return new StatusDto($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof StatusDto) {
            return $value->value();
        }

        return $value;
    }
}
