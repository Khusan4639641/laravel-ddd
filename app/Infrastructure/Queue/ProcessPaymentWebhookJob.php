<?php

namespace App\Infrastructure\Queue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload) {}

    public function handle(): void
    {
        Log::info('Processing payment webhook.', $this->payload);
    }
}
