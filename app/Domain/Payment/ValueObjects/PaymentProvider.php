<?php

namespace App\Domain\Payment\ValueObjects;

use InvalidArgumentException;

final readonly class PaymentProvider
{
    public const FAKE = 'fake';

    private string $value;

    public function __construct(string $value)
    {
        $provider = strtolower(trim($value));

        if ($provider !== self::FAKE) {
            throw new InvalidArgumentException('Invalid payment provider.');
        }

        $this->value = $provider;
    }

    public static function fake(): self
    {
        return new self(self::FAKE);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
