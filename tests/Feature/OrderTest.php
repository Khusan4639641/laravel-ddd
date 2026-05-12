<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(price: 500, stock: 5);

        $this
            ->withUserToken($user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', 1000)
            ->assertJsonPath('data.items.0.product_id', $product->id)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.price', 500)
            ->assertJsonPath('data.items.0.subtotal', 1000);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'total_amount' => 1000,
        ]);
    }

    public function test_user_cannot_create_order_without_auth(): void
    {
        $product = $this->createProduct();

        $this
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->assertUnauthorized();
    }

    public function test_user_cannot_order_inactive_product(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(status: 'inactive');

        $this
            ->withUserToken($user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_user_cannot_order_product_if_stock_is_empty(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 0);

        $this
            ->withUserToken($user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_user_cannot_order_more_than_available_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 2);

        $this
            ->withUserToken($user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_creating_order_reduces_product_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 4);

        $this
            ->withUserToken($user)
            ->postJson('/api/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertCreated();

        $this->assertSame(1, $product->refresh()->stock);
    }

    public function test_user_can_list_own_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = $this->createProduct();

        $ownOrder = $this->createOrderFor($user, $product);
        $this->createOrderFor($otherUser, $product);

        $this
            ->withUserToken($user)
            ->getJson('/api/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownOrder->id)
            ->assertJsonPath('data.0.user_id', $user->id);
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = $this->createProduct();
        $order = $this->createOrderFor($otherUser, $product);

        $this
            ->withUserToken($user)
            ->getJson("/api/orders/{$order->id}")
            ->assertNotFound();
    }

    public function test_user_can_cancel_pending_order(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 3);
        $order = $this->createOrderFor($user, $product, status: 'pending', quantity: 2);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancelling_order_restores_product_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 3);
        $order = $this->createOrderFor($user, $product, status: 'pending', quantity: 2);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/cancel")
            ->assertOk();

        $this->assertSame(5, $product->refresh()->stock);
    }

    public function test_user_cannot_cancel_paid_order(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(stock: 3);
        $order = $this->createOrderFor($user, $product, status: 'paid', quantity: 2);

        $this
            ->withUserToken($user)
            ->postJson("/api/orders/{$order->id}/cancel")
            ->assertStatus(422);
    }

    public function test_admin_can_list_all_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();

        $this->createOrderFor(User::factory()->create(), $product);
        $this->createOrderFor(User::factory()->create(), $product);

        $this
            ->withUserToken($admin)
            ->getJson('/api/admin/orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_view_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $order = $this->createOrderFor(User::factory()->create(), $product);

        $this
            ->withUserToken($admin)
            ->getJson("/api/admin/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_admin_can_complete_paid_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->createProduct();
        $order = $this->createOrderFor(User::factory()->create(), $product, status: 'paid');

        $this
            ->withUserToken($admin)
            ->postJson("/api/admin/orders/{$order->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    private function createProduct(
        int $price = 500,
        int $stock = 5,
        string $status = 'active',
    ): ProductModel {
        return ProductModel::query()->create([
            'name' => 'Test Product',
            'description' => null,
            'price' => $price,
            'stock' => $stock,
            'status' => $status,
        ]);
    }

    private function createOrderFor(
        User $user,
        ProductModel $product,
        string $status = 'pending',
        int $quantity = 1,
    ): OrderModel {
        $order = OrderModel::query()->create([
            'user_id' => $user->id,
            'status' => $status,
            'total_amount' => $quantity * $product->price,
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
