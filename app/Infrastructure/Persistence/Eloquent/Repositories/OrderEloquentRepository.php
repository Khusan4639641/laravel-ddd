<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Exceptions\InsufficientStockException;
use App\Domain\Order\Exceptions\InvalidOrderStatusException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Product\Events\ProductStockReduced;
use App\Domain\Product\Events\ProductStockRestored;
use App\Domain\Product\ValueObjects\ProductStatus;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

final class OrderEloquentRepository implements OrderRepositoryInterface
{
    public function create(int $userId, array $items): Order
    {
        $order = DB::transaction(function () use ($userId, $items): Order {
            $items = $this->aggregateItems($items);

            if ($items === []) {
                throw new InvalidArgumentException('Order must contain at least one item.');
            }

            $totalAmount = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                $product = ProductModel::query()
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if ($product === null || $product->status !== ProductStatus::ACTIVE) {
                    throw new InsufficientStockException("Product {$item['product_id']} is not available.");
                }

                if ((int) $product->stock < $item['quantity']) {
                    throw new InsufficientStockException("Insufficient stock for product {$item['product_id']}.");
                }

                $subtotal = $item['quantity'] * (int) $product->price;
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => (int) $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = OrderModel::query()->create([
                'user_id' => $userId,
                'status' => OrderStatus::PENDING,
                'total_amount' => $totalAmount,
            ]);

            foreach ($preparedItems as $item) {
                /** @var ProductModel $product */
                $product = $item['product'];

                OrderItemModel::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $product->stock = (int) $product->stock - $item['quantity'];
                $product->save();

                Event::dispatch(new ProductStockReduced(
                    productId: (int) $product->id,
                    orderId: (int) $order->id,
                    quantity: (int) $item['quantity'],
                    stock: (int) $product->stock,
                ));
            }

            return $this->toDomain($order->load('items'));
        });

        Event::dispatch(new OrderCreated(
            orderId: $order->id(),
            userId: $order->userId(),
            totalAmount: $order->totalAmount(),
            items: $this->eventItems($order),
        ));

        return $order;
    }

    public function allForUser(int $userId): array
    {
        return OrderModel::query()
            ->where('user_id', $userId)
            ->with('items')
            ->latest('id')
            ->get()
            ->map(fn (OrderModel $order): Order => $this->toDomain($order))
            ->all();
    }

    public function all(): array
    {
        return OrderModel::query()
            ->with('items')
            ->latest('id')
            ->get()
            ->map(fn (OrderModel $order): Order => $this->toDomain($order))
            ->all();
    }

    public function findForUser(int $id, int $userId): ?Order
    {
        $order = OrderModel::query()
            ->where('user_id', $userId)
            ->with('items')
            ->find($id);

        return $order === null ? null : $this->toDomain($order);
    }

    public function findById(int $id): ?Order
    {
        $order = OrderModel::query()
            ->with('items')
            ->find($id);

        return $order === null ? null : $this->toDomain($order);
    }

    public function cancelForUser(int $id, int $userId): ?Order
    {
        $order = DB::transaction(function () use ($id, $userId): ?Order {
            $order = OrderModel::query()
                ->where('user_id', $userId)
                ->with('items')
                ->lockForUpdate()
                ->find($id);

            if ($order === null) {
                return null;
            }

            if ($order->status !== OrderStatus::PENDING) {
                throw new InvalidOrderStatusException('Only pending orders can be cancelled.');
            }

            foreach ($order->items as $item) {
                $product = ProductModel::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($product !== null) {
                    $product->stock = (int) $product->stock + (int) $item->quantity;
                    $product->save();

                    Event::dispatch(new ProductStockRestored(
                        productId: (int) $product->id,
                        orderId: (int) $order->id,
                        quantity: (int) $item->quantity,
                        stock: (int) $product->stock,
                    ));
                }
            }

            $order->status = OrderStatus::CANCELLED;
            $order->save();

            return $this->toDomain($order->refresh()->load('items'));
        });

        if ($order !== null) {
            Event::dispatch(new OrderCancelled(
                orderId: $order->id(),
                userId: $order->userId(),
                totalAmount: $order->totalAmount(),
                items: $this->eventItems($order),
            ));
        }

        return $order;
    }

    public function complete(int $id): ?Order
    {
        $order = OrderModel::query()
            ->with('items')
            ->find($id);

        if ($order === null) {
            return null;
        }

        if ($order->status !== OrderStatus::PAID) {
            throw new InvalidOrderStatusException('Only paid orders can be completed.');
        }

        $order->status = OrderStatus::COMPLETED;
        $order->save();

        return $this->toDomain($order->refresh()->load('items'));
    }

    /**
     * @param  list<array{product_id: int, quantity: int}>  $items
     * @return list<array{product_id: int, quantity: int}>
     */
    private function aggregateItems(array $items): array
    {
        $aggregated = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];

            if (! isset($aggregated[$productId])) {
                $aggregated[$productId] = [
                    'product_id' => $productId,
                    'quantity' => 0,
                ];
            }

            $aggregated[$productId]['quantity'] += (int) $item['quantity'];
        }

        return array_values($aggregated);
    }

    private function toDomain(OrderModel $order): Order
    {
        $items = $order->relationLoaded('items')
            ? $order->items
            : new Collection;

        return new Order(
            id: (int) $order->id,
            userId: (int) $order->user_id,
            status: new OrderStatus($order->status),
            totalAmount: (int) $order->total_amount,
            items: $items
                ->map(fn (OrderItemModel $item): OrderItem => $this->toDomainItem($item))
                ->all(),
            createdAt: $order->created_at?->toISOString(),
            updatedAt: $order->updated_at?->toISOString(),
        );
    }

    private function toDomainItem(OrderItemModel $item): OrderItem
    {
        return new OrderItem(
            id: (int) $item->id,
            orderId: (int) $item->order_id,
            productId: (int) $item->product_id,
            quantity: (int) $item->quantity,
            price: (int) $item->price,
            subtotal: (int) $item->subtotal,
            createdAt: $item->created_at?->toISOString(),
            updatedAt: $item->updated_at?->toISOString(),
        );
    }

    /**
     * @return list<array{product_id: int, quantity: int, price: int, subtotal: int}>
     */
    private function eventItems(Order $order): array
    {
        return array_map(
            fn (OrderItem $item): array => [
                'product_id' => $item->productId(),
                'quantity' => $item->quantity(),
                'price' => $item->price(),
                'subtotal' => $item->subtotal(),
            ],
            $order->items(),
        );
    }
}
