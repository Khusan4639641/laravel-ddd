<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_view_active_products(): void
    {
        ProductModel::query()->create([
            'name' => 'Active Product',
            'description' => 'Visible',
            'price' => 1000,
            'stock' => 10,
            'status' => 'active',
        ]);
        ProductModel::query()->create([
            'name' => 'Inactive Product',
            'description' => 'Hidden',
            'price' => 1500,
            'stock' => 0,
            'status' => 'inactive',
        ]);

        $this
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Active Product')
            ->assertJsonPath('data.0.status', 'active');
    }

    public function test_public_user_cannot_view_inactive_product(): void
    {
        $product = ProductModel::query()->create([
            'name' => 'Inactive Product',
            'description' => null,
            'price' => 1500,
            'stock' => 0,
            'status' => 'inactive',
        ]);

        $this
            ->getJson("/api/products/{$product->id}")
            ->assertNotFound();
    }

    public function test_admin_can_list_all_products(): void
    {
        ProductModel::query()->create([
            'name' => 'Active Product',
            'description' => 'Visible',
            'price' => 1000,
            'stock' => 10,
            'status' => 'active',
        ]);
        ProductModel::query()->create([
            'name' => 'Inactive Product',
            'description' => 'Hidden',
            'price' => 1500,
            'stock' => 0,
            'status' => 'inactive',
        ]);

        $this
            ->withAdminToken()
            ->getJson('/api/admin/products')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['status' => 'active'])
            ->assertJsonFragment(['status' => 'inactive']);
    }

    public function test_admin_can_create_product(): void
    {
        $this
            ->withAdminToken()
            ->postJson('/api/admin/products', [
                'name' => 'New Product',
                'description' => 'New description',
                'price' => 2000,
                'stock' => 5,
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Product')
            ->assertJsonPath('data.description', 'New description')
            ->assertJsonPath('data.price', 2000)
            ->assertJsonPath('data.stock', 5)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 2000,
            'stock' => 5,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = ProductModel::query()->create([
            'name' => 'Old Product',
            'description' => 'Old description',
            'price' => 1000,
            'stock' => 10,
            'status' => 'active',
        ]);

        $this
            ->withAdminToken()
            ->putJson("/api/admin/products/{$product->id}", [
                'name' => 'Updated Product',
                'description' => null,
                'price' => 2500,
                'stock' => 3,
                'status' => 'inactive',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Product')
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.price', 2500)
            ->assertJsonPath('data.stock', 3)
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'description' => null,
            'price' => 2500,
            'stock' => 3,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = ProductModel::query()->create([
            'name' => 'Deleted Product',
            'description' => null,
            'price' => 1000,
            'stock' => 10,
            'status' => 'active',
        ]);

        $this
            ->withAdminToken()
            ->deleteJson("/api/admin/products/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_normal_user_cannot_access_admin_product_routes(): void
    {
        $product = ProductModel::query()->create([
            'name' => 'Protected Product',
            'description' => null,
            'price' => 1000,
            'stock' => 10,
            'status' => 'active',
        ]);
        $token = User::factory()->create(['role' => 'user'])->createToken('test-token')->plainTextToken;

        $routes = [
            ['GET', '/api/admin/products', []],
            ['POST', '/api/admin/products', [
                'name' => 'Forbidden Product',
                'price' => 2000,
                'stock' => 5,
                'status' => 'active',
            ]],
            ['GET', "/api/admin/products/{$product->id}", []],
            ['PUT', "/api/admin/products/{$product->id}", [
                'name' => 'Forbidden Update',
            ]],
            ['DELETE', "/api/admin/products/{$product->id}", []],
        ];

        foreach ($routes as [$method, $uri, $payload]) {
            $this
                ->withHeader('Authorization', 'Bearer '.$token)
                ->json($method, $uri, $payload)
                ->assertForbidden();
        }
    }

    private function withAdminToken(): self
    {
        $token = User::factory()->create(['role' => 'admin'])->createToken('test-token')->plainTextToken;

        return $this->withHeader('Authorization', 'Bearer '.$token);
    }
}
