<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductPromotionModel extends Model
{
    protected $table = 'product_promotions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'product_id',
        'discount_percentage',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    /** Scope: promotions that are currently active and within their date window. */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
