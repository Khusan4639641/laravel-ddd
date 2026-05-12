<?php

namespace App\Domain\Order\ValueObjects;

use InvalidArgumentException;

final readonly class Quantity
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }
}
