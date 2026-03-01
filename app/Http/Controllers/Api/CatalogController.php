<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListProductsRequest;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use Illuminate\Http\JsonResponse;

final class CatalogController extends Controller
{
    public function index(ListProductsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = ProductModel::query()->with(['gallery', 'category', 'company', 'activePromotion']);

        // ── Filters ───────────────────────────────────────────────────────────
        if (!empty($validated['categoryId'])) {
            $query->where('category_id', $validated['categoryId']);
        }

        if (!empty($validated['companyId'])) {
            $query->where('company_id', $validated['companyId']);
        }

        if (!empty($validated['q'])) {
            $query->where('name', 'like', '%' . $validated['q'] . '%');
        }

        if (!empty($validated['minPrice'])) {
            // minPrice is passed in BRL cents from the frontend
            $query->where('price_amount', '>=', (int) $validated['minPrice']);
        }

        if (!empty($validated['maxPrice'])) {
            $query->where('price_amount', '<=', (int) $validated['maxPrice']);
        }

        if (!empty($validated['onlyWithPromotion'])) {
            $query->whereHas('activePromotion');
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        $sortBy  = $validated['sortBy'] ?? 'name';
        $sortDir = $validated['sortDir'] ?? 'asc';

        $query->orderBy(match ($sortBy) {
            'price' => 'price_amount',
            'name'  => 'name',
            default => 'name',
        }, in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'asc');

        // ── Pagination ────────────────────────────────────────────────────────
        $perPage = min((int) ($validated['perPage'] ?? 15), 50);
        $page    = (int) ($validated['page'] ?? 1);

        $paginator = $query
            ->paginate(perPage: $perPage, page: $page)
            ->through(fn (ProductModel $product): array => $this->toProductDto($product));

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'total'       => $paginator->total(),
                'perPage'     => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'lastPage'    => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $query = ProductModel::query()
            ->with(['gallery', 'category', 'company', 'activePromotion']);

        // Accept both a friendly slug and a raw UUID (backwards-compat with old links).
        $isUuid  = (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $slug);
        $product = $isUuid
            ? $query->where('id', $slug)->firstOrFail()
            : $query->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $this->toProductDto($product)]);
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'data' => CategoryModel::query()
                ->orderBy('name')
                ->get()
                ->unique('name')
                ->map(static fn (CategoryModel $category): array => [
                    'id'   => $category->id,
                    'name' => $category->name,
                ])
                ->values(),
        ]);
    }

    private function toProductDto(ProductModel $product): array
    {
        $promotion   = $product->relationLoaded('activePromotion') ? $product->activePromotion : null;
        $priceAmount = $product->price_amount;
        $discountPct = $promotion?->discount_percentage;

        return [
            'id'           => $product->id,
            'slug'         => $product->slug,
            'name'         => $product->name,
            'description'  => $product->description ?? null,
            'categoryId'   => $product->category_id,
            'categoryName' => $product->relationLoaded('category') ? $product->category?->name : null,
            'companyId'    => $product->company_id,
            'companyName'  => $product->relationLoaded('company') ? $product->company?->trade_name : null,
            'price'        => [
                'amount'   => $priceAmount,
                'currency' => $product->price_currency,
            ],
            'promotion' => $promotion ? [
                'id'                 => $promotion->id,
                'discountPercentage' => $discountPct,
                'originalAmount'     => $priceAmount,
                'discountedAmount'   => (int) round($priceAmount * (1 - $discountPct / 100)),
                'endsAt'             => $promotion->ends_at->toISOString(),
            ] : null,
            'images' => $product->relationLoaded('gallery')
                ? $product->gallery->pluck('url')->values()->all()
                : [],
        ];
    }
}
