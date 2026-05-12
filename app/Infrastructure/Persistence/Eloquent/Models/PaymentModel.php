<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'user_id', 'amount', 'status', 'provider', 'transaction_id'])]
final class PaymentModel extends Model
{
    protected $table = 'payments';

    /**
     * @return BelongsTo<OrderModel, PaymentModel>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'user_id' => 'integer',
            'amount' => 'integer',
        ];
    }
}
