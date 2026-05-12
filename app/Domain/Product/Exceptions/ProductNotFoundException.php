<?php

namespace App\Domain\Product\Exceptions;

use DomainException;

final class ProductNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("Product {$id} was not found.");
    }
}
