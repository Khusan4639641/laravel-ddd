<?php

namespace App\Domain\Order\Exceptions;

use DomainException;

final class InsufficientStockException extends DomainException
{
    public function __construct(string $message = 'Insufficient product stock.')
    {
        parent::__construct($message);
    }
}
