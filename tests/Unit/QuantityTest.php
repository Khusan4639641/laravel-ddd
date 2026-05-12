<?php

namespace Tests\Unit;

use App\Domain\Order\ValueObjects\Quantity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QuantityTest extends TestCase
{
    public function test_quantity_value_object_rejects_zero_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Quantity(0);
    }

    public function test_quantity_value_object_rejects_negative_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Quantity(-1);
    }
}
