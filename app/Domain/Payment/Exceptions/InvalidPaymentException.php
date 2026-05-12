<?php

namespace App\Domain\Payment\Exceptions;

use DomainException;

final class InvalidPaymentException extends DomainException
{
    public function __construct(string $message = 'Invalid payment.')
    {
        parent::__construct($message);
    }
}
