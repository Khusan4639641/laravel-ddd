<?php

namespace Tests\Unit;

use App\Domain\Payment\ValueObjects\PaymentProvider;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PaymentProviderTest extends TestCase
{
    public function test_payment_provider_value_object_rejects_invalid_provider(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PaymentProvider('stripe');
    }
}
