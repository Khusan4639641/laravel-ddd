<?php

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

final readonly class UserRole
{
    public const USER = 'user';

    public const ADMIN = 'admin';

    private string $value;

    public function __construct(string $value)
    {
        $role = strtolower(trim($value));

        if (! in_array($role, [self::USER, self::ADMIN], true)) {
            throw new InvalidArgumentException('Invalid user role.');
        }

        $this->value = $role;
    }

    public static function user(): self
    {
        return new self(self::USER);
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
