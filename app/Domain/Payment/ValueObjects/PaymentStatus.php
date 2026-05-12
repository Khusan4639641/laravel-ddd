<?php

namespace App\Domain\Payment\ValueObjects;

use InvalidArgumentException;

final readonly class PaymentStatus
{
    public const PENDING = 'pending';

    public const SUCCESS = 'success';

    public const FAILED = 'failed';

    private string $value;

    public function __construct(string $value)
    {
        $status = strtolower(trim($value));

        if (! in_array($status, [self::PENDING, self::SUCCESS, self::FAILED], true)) {
            throw new InvalidArgumentException('Invalid payment status.');
        }

        $this->value = $status;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function success(): self
    {
        return new self(self::SUCCESS);
    }

    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isSuccess(): bool
    {
        return $this->value === self::SUCCESS;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
