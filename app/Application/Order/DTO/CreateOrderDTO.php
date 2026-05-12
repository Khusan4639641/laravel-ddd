<?php

namespace App\Application\Order\DTO;

use InvalidArgumentException;

final readonly class CreateOrderDTO
{
    /**
     * @param  list<CreateOrderItemDTO>  $items
     */
    public function __construct(public array $items)
    {
        if ($this->items === []) {
            throw new InvalidArgumentException('Order must contain at least one item.');
        }
    }

    /**
     * @param  array{items: list<array{product_id: int, quantity: int}>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            items: array_map(
                fn (array $item): CreateOrderItemDTO => CreateOrderItemDTO::fromArray($item),
                $data['items'],
            ),
        );
    }

    /**
     * @return list<array{product_id: int, quantity: int}>
     */
    public function toRepositoryData(): array
    {
        return array_map(
            fn (CreateOrderItemDTO $item): array => $item->toRepositoryData(),
            $this->items,
        );
    }
}
