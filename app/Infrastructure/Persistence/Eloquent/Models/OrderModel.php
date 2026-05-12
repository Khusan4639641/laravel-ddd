<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'status', 'total_amount'])]
final class OrderModel extends Model
{
    protected $table = 'orders';

    /**
     * @return HasMany<OrderItemModel>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'total_amount' => 'integer',
        ];
    }
}
