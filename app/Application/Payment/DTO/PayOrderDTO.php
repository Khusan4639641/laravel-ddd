<?php

namespace App\Application\Payment\DTO;

use App\Domain\Payment\ValueObjects\PaymentProvider;
use InvalidArgumentException;

final readonly class PayOrderDTO
{
    public PaymentProvider $provider;

    public function __construct(
        public int $amount,
        string $provider,
    ) {
        if ($this->amount < 1) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $this->provider = new PaymentProvider($provider);
    }

    /**
     * @param  array{amount: int, provider: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: (int) $data['amount'],
            provider: $data['provider'],
        );
    }
}
