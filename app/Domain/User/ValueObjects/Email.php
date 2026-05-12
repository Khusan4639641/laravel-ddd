<?php

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

final readonly class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $email = strtolower(trim($value));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        $this->value = $email;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $email): bool
    {
        return $this->value === $email->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
