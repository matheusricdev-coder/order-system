<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Common\UuidGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\ProductModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class AdminProductController extends Controller
{
    public function __construct(private readonly UuidGenerator $uuid)
    {
    }

    public function index(): JsonResponse
    {
        $products = ProductModel::query()
            ->with(['category', 'company'])
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $products->map(fn (ProductModel $p) => $this->toDto($p))->values(),
            'meta' => [
                'total'       => $products->total(),
                'perPage'     => $products->perPage(),
                'currentPage' => $products->currentPage(),
                'lastPage'    => $products->lastPage(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $product = ProductModel::query()->with(['gallery', 'category', 'company'])->findOrFail($id);

        return response()->json(['data' => $this->toDto($product)]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $id   = $this->uuid->generate();
        $slug = Str::slug($validated['name']) . '-' . substr(str_replace('-', '', $id), 0, 6);

        /** @var ProductModel $product */
        $product = ProductModel::query()->create([
            'id'             => $id,
            'name'           => $validated['name'],
            'slug'           => $slug,
            'description'    => $validated['description'] ?? null,
            'category_id'    => $validated['categoryId'],
            'company_id'     => $validated['companyId'],
            'price_amount'   => (int) $validated['priceAmount'],
            'price_currency' => $validated['priceCurrency'] ?? 'BRL',
        ]);

        return response()->json(['data' => $this->toDto($product)], 201);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        /** @var ProductModel $product */
        $product = ProductModel::query()->findOrFail($id);

        $validated = $request->validated();

        $updates = array_filter([
            'name'           => $validated['name'] ?? null,
            'description'    => $validated['description'] ?? null,
            'category_id'    => $validated['categoryId'] ?? null,
            'price_amount'   => isset($validated['priceAmount']) ? (int) $validated['priceAmount'] : null,
            'price_currency' => $validated['priceCurrency'] ?? null,
        ], fn ($v) => $v !== null);

        // Regenerate slug when name changes
        if (isset($updates['name'])) {
            $updates['slug'] = Str::slug($updates['name']) . '-' . substr(str_replace('-', '', $product->id), 0, 6);
        }

        $product->update($updates);

        $product->refresh();

        return response()->json(['data' => $this->toDto($product)]);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = ProductModel::query()->findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }

    private function toDto(ProductModel $p): array
    {
        return [
            'id'           => $p->id,
            'slug'         => $p->slug,
            'name'         => $p->name,
            'description'  => $p->description ?? null,
            'categoryId'   => $p->category_id,
            'categoryName' => $p->relationLoaded('category') ? $p->category?->name : null,
            'companyId'    => $p->company_id,
            'companyName'  => $p->relationLoaded('company') ? $p->company?->trade_name : null,
            'price'        => [
                'amount'   => $p->price_amount,
                'currency' => $p->price_currency,
            ],
            'images' => $p->relationLoaded('gallery')
                ? $p->gallery->pluck('url')->values()->all()
                : [],
            'createdAt' => $p->created_at?->toISOString(),
            'updatedAt' => $p->updated_at?->toISOString(),
        ];
    }
}
