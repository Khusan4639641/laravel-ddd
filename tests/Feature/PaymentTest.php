<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_pay_pending_order(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderFor($user, status: 'pending', totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertCreated()
            ->assertJsonPath('data.order_id', $order->id)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.amount', 1000)
            ->assertJsonPath('data.status', 'success')
            ->assertJsonPath('data.provider', 'fake')
            ->assertJsonStructure([
                'data' => ['id', 'order_id', 'user_id', 'amount', 'status', 'provider', 'transaction_id'],
            ]);
    }

    public function test_payment_creates_success_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderFor($user, totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertCreated();

        $payment = PaymentModel::query()->firstOrFail();

        $this->assertSame($order->id, $payment->order_id);
        $this->assertSame($user->id, $payment->user_id);
        $this->assertSame(1000, $payment->amount);
        $this->assertSame('success', $payment->status);
        $this->assertSame('fake', $payment->provider);
        $this->assertNotNull($payment->transaction_id);
    }

    public function test_order_becomes_paid_after_successful_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderFor($user, totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertCreated();

        $this->assertSame('paid', $order->refresh()->status);
    }

    public function test_user_cannot_pay_order_twice(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderFor($user, totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertCreated();

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertStatus(422);
    }

    public function test_user_cannot_pay_another_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = $this->createOrderFor($otherUser, totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertNotFound();
    }

    public function test_user_cannot_pay_cancelled_order(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderFor($user, status: 'cancelled', totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertStatus(422);
    }

    public function test_user_cannot_pay_completed_order(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderFor($user, status: 'completed', totalAmount: 1000);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertStatus(422);
    }

    public function test_admin_cannot_pay_user_order(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createOrderFor($user, totalAmount: 1000);

        $this
            ->withUserToken($admin)
            ->postJson("/api/orders/{$order->id}/pay", [
                'amount' => 1000,
                'provider' => 'fake',
            ])
            ->assertForbidden();
    }

    public function test_user_can_list_own_payments(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $payment = $this->createPaymentFor($user);
        $this->createPaymentFor($otherUser);

        $this
            ->withUserToken($user)
            ->getJson('/api/payments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $payment->id)
            ->assertJsonPath('data.0.user_id', $user->id);
    }

    public function test_user_cannot_view_another_users_payment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $payment = $this->createPaymentFor($otherUser);

        $this
            ->withUserToken($user)
            ->getJson("/api/payments/{$payment->id}")
            ->assertNotFound();
    }

    private function createOrderFor(
        User $user,
        string $status = 'pending',
        int $totalAmount = 1000,
    ): OrderModel {
        $product = ProductModel::query()->create([
            'name' => 'Payment Product',
            'description' => null,
            'price' => $totalAmount,
            'stock' => 10,
            'status' => 'active',
        ]);

        $order = OrderModel::query()->create([
            'user_id' => $user->id,
            'status' => $status,
            'total_amount' => $totalAmount,
        ]);

        OrderItemModel::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $totalAmount,
            'subtotal' => $totalAmount,
        ]);

        return $order;
    }

    private function createPaymentFor(User $user): PaymentModel
    {
        $order = $this->createOrderFor($user, status: 'paid', totalAmount: 1000);

        return PaymentModel::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'success',
            'provider' => 'fake',
            'transaction_id' => 'fake_test',
        ]);
    }

    private function withUserToken(User $user): self
    {
        return $this->withHeader('Authorization', 'Bearer '.$user->createToken('test-token')->plainTextToken);
    }
}
