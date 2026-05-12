<?php

namespace App\Domain\Payment\Exceptions;

use DomainException;

final class PaymentAlreadyProcessedException extends DomainException
{
    public function __construct(string $message = 'Payment has already been processed.')
    {
        parent::__construct($message);
    }
}
