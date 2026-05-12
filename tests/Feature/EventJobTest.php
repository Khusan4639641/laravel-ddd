<?php

namespace Tests\Feature;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Payment\Events\PaymentFailed;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Queue\SendOrderPaidEmailJob;
use App\Infrastructure\Queue\WriteOrderLogJob;
use App\Infrastructure\Services\FakePaymentProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_created_event_dispatched_after_order_creation(): void
    {
        Event::fake([OrderCreated::class]);

        $user = User::factory()->create();
        $product = $this->createProduct();

        $this
            ->withUserToken($user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
            ->assertCreated();

        Event::assertDispatched(OrderCreated::class, fn (OrderCreated $event): bool => $event->userId === $user->id
            && $event->totalAmount === 1000
            && $event->items[0]['product_id'] === $product->id
            && $event->items[0]['quantity'] === 2);
    }

    public function test_order_cancelled_event_dispatched_after_order_cancellation(): void
    {
        Event::fake([OrderCancelled::class]);

        $user = User::factory()->create();
        $product = $this->createProduct();
        $order = $this->createOrderFor($user, $product, status: 'pending');

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/cancel")
            ->assertOk();

        Event::assertDispatched(OrderCancelled::class, fn (OrderCancelled $event): bool => $event->orderId === $order->id
            && $event->userId === $user->id);
    }

    public function test_order_paid_event_dispatched_after_payment(): void
    {
        Event::fake([OrderPaid::class]);

        $user = User::factory()->create();
        $order = $this->createOrderFor($user, $this->createProduct(), totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertCreated();

        Event::assertDispatched(OrderPaid::class, fn (OrderPaid $event): bool => $event->orderId === $order->id
            && $event->userId === $user->id
            && $event->amount === 1000);
    }

    public function test_send_order_paid_email_job_pushed_after_successful_payment(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $order = $this->createOrderFor($user, $this->createProduct(), totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertCreated();

        Queue::assertPushed(SendOrderPaidEmailJob::class, fn (SendOrderPaidEmailJob $job): bool => $job->orderId === $order->id
            && $job->userId === $user->id
            && $job->amount === 1000);
    }

    public function test_write_order_log_job_pushed_after_order_creation_and_cancellation(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = $this->createProduct();

        $createResponse = $this
            ->withUserToken($user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->assertCreated();

        $orderId = (int) $createResponse->json('data.id');

        Queue::assertPushed(WriteOrderLogJob::class, fn (WriteOrderLogJob $job): bool => $job->type === 'order.created'
            && $job->context['order_id'] === $orderId);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$orderId}/cancel")
            ->assertOk();

        Queue::assertPushed(WriteOrderLogJob::class, fn (WriteOrderLogJob $job): bool => $job->type === 'order.cancelled'
            && $job->context['order_id'] === $orderId);
    }

    public function test_payment_failed_event_can_be_dispatched_for_failed_fake_payment(): void
    {
        Event::fake([PaymentFailed::class]);

        $this->app->bind(FakePaymentProvider::class, fn (): FakePaymentProvider => new class extends FakePaymentProvider
        {
            public function charge(int $amount): array
            {
                return [
                    'successful' => false,
                    'transaction_id' => null,
                ];
            }
        });

        $user = User::factory()->create();
        $order = $this->createOrderFor($user, $this->createProduct(), totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'failed');

        Event::assertDispatched(PaymentFailed::class, fn (PaymentFailed $event): bool => $event->orderId === $order->id
            && $event->userId === $user->id
            && $event->amount === 1000);
    }

    private function createProduct(int $price = 500, int $stock = 10): ProductModel
    {
        return ProductModel::query()->create([
            'name' => 'Event Product',
            'description' => null,
            'price' => $price,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }

    private function createOrderFor(
        User $user,
        ProductModel $product,
        string $status = 'pending',
        int $quantity = 1,
        ?int $totalAmount = null,
    ): OrderModel {
        $totalAmount ??= $quantity * $product->price;

        $order = OrderModel::query()->create([
            'user_id' => $user->id,
            'status' => $status,
            'total_amount' => $totalAmount,
        ]);

        OrderItemModel::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'subtotal' => $quantity * $product->price,
        ]);

        return $order;
    }

    private function withUserToken(User $user): self
    {
        return $this->withHeader('Authorization', 'Bearer '.$user->createToken('test-token')->plainTextToken);
    }
}
