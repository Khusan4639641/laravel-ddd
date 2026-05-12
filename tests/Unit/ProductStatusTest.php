<?php

namespace Tests\Unit;

use App\Domain\Product\ValueObjects\ProductStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProductStatusTest extends TestCase
{
    public function test_product_status_value_object_rejects_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ProductStatus('archived');
    }
}
