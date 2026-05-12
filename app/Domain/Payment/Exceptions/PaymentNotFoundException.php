<?php

namespace App\Domain\Payment\Exceptions;

use DomainException;

final class PaymentNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("Payment {$id} was not found.");
    }
}
