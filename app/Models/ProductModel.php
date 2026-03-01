<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ProductModel extends Model
{
    protected $table = 'products';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'price_amount',
        'price_currency',
        'category_id',
        'company_id',
    ];

    public function gallery(): HasMany
    {
        return $this->hasMany(ProductGalleryModel::class, 'product_id')
            ->orderBy('position');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    /** Returns the currently active promotion (if any). */
    public function activePromotion(): HasOne
    {
        return $this->hasOne(ProductPromotionModel::class, 'product_id')
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('starts_at');
    }
}
