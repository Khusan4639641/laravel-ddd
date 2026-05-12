<?php

namespace Tests\Unit;

use App\Domain\Payment\ValueObjects\PaymentStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_payment_status_value_object_rejects_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PaymentStatus('refunded');
    }
}
