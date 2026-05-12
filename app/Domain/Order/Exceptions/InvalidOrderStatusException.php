<?php

namespace App\Domain\Order\Exceptions;

use DomainException;

final class InvalidOrderStatusException extends DomainException
{
    public function __construct(string $message = 'Invalid order status transition.')
    {
        parent::__construct($message);
    }
}
