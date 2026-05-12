<?php

namespace App\Infrastructure\Services;

use Illuminate\Support\Str;

final class FakePaymentProvider
{
    /**
     * @return array{successful: bool, transaction_id: string|null}
     */
    public function charge(int $amount): array
    {
        return [
            'successful' => true,
            'transaction_id' => 'fake_'.Str::uuid()->toString(),
        ];
    }
}
