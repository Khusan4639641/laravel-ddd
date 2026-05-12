<?php

namespace Tests\Unit;

use App\Domain\Order\ValueObjects\OrderStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_order_status_value_object_rejects_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OrderStatus('refunded');
    }
}
