<?php

namespace Tests\Unit;

use App\Domain\User\ValueObjects\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_email_value_object_lowercases_email(): void
    {
        $email = new Email('John.Doe@Example.COM');

        $this->assertSame('john.doe@example.com', $email->value());
    }

    public function test_invalid_email_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('invalid-email');
    }
}
