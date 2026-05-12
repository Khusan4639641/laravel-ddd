<?php

namespace App\Domain\Product\ValueObjects;

use InvalidArgumentException;

final readonly class ProductStatus
{
    public const ACTIVE = 'active';

    public const INACTIVE = 'inactive';

    private string $value;

    public function __construct(string $value)
    {
        $status = strtolower(trim($value));

        if (! in_array($status, [self::ACTIVE, self::INACTIVE], true)) {
            throw new InvalidArgumentException('Invalid product status.');
        }

        $this->value = $status;
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
