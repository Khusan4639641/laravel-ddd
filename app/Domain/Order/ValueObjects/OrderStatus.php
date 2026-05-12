<?php

namespace App\Domain\Order\ValueObjects;

use InvalidArgumentException;

final readonly class OrderStatus
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const CANCELLED = 'cancelled';

    public const COMPLETED = 'completed';

    private string $value;

    public function __construct(string $value)
    {
        $status = strtolower(trim($value));

        if (! in_array($status, [self::PENDING, self::PAID, self::CANCELLED, self::COMPLETED], true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }

        $this->value = $status;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
