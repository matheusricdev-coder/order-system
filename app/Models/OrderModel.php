<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

final class OrderModel extends Model
{
    protected $table = 'orders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'status',
        'payment_intent_id',
        'order_number',
    ];

    protected $casts = [
        'order_number' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $order): void {
            if ($order->order_number === null) {
                $next = (int) DB::table('orders')->max('order_number') + 1;
                $order->order_number = $next;
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}

