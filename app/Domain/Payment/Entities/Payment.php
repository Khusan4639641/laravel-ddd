<?php

namespace App\Domain\Payment\Entities;

use App\Domain\Payment\ValueObjects\PaymentProvider;
use App\Domain\Payment\ValueObjects\PaymentStatus;

final readonly class Payment
{
    public function __construct(
        private int $id,
        private int $orderId,
        private int $userId,
        private int $amount,
        private PaymentStatus $status,
        private PaymentProvider $provider,
        private ?string $transactionId = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }

    public function provider(): PaymentProvider
    {
        return $this->provider;
    }

    public function transactionId(): ?string
    {
        return $this->transactionId;
    }

    public function createdAt(): ?string
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
