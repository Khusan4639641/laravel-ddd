<?php

namespace App\Domain\Order\Exceptions;

use DomainException;

final class OrderNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("Order {$id} was not found.");
    }
}
