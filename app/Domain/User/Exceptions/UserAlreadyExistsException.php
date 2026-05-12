<?php

namespace App\Domain\User\Exceptions;

use App\Domain\User\ValueObjects\Email;
use DomainException;

final class UserAlreadyExistsException extends DomainException
{
    public function __construct(Email $email)
    {
        parent::__construct("User with email {$email->value()} already exists.");
    }
}
