<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Order\GetOrder\GetOrderReadModelRepository;
use App\Application\Order\ListOrders\ListOrdersReadModelRepository;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Exceptions\UnauthorizedOrderException;
use App\Models\OrderItemModel;
use App\Models\OrderModel;

final class EloquentOrderReadModelRepository implements GetOrderReadModelRepository, ListOrdersReadModelRepository
{
    public function getByIdForRequester(string $orderId, string $requesterId): array
    {
        /** @var OrderModel|null $order */
        $order = OrderModel::query()
            ->with('items.product')
            ->find($orderId);

        if ($order === null) {
            throw OrderNotFoundException::withId($orderId);
        }

        if ((string) $order->user_id !== $requesterId) {
            throw UnauthorizedOrderException::notOwner($orderId);
        }

        return $this->toReadDTO($order);
    }

    public function listForRequester(string $requesterId, ?string $status, int $perPage, int $page): array
    {
        $result = OrderModel::query()
            ->with('items.product')
            ->where('user_id', $requesterId)
            ->when(
                $status !== null,
                static fn($query) => $query->where('status', $status)
            )
            ->orderByDesc('created_at')
            ->paginate(perPage: $perPage, page: $page);

        return $result->through(fn(OrderModel $order) => $this->toReadDTO($order))->toArray();
    }

    private function toReadDTO(OrderModel $order): array
    {
        $items = $order->items->map(static function (OrderItemModel $item): array {
            $lineTotal = $item->quantity * $item->unit_price_amount;

            return [
                'productId'  => $item->product_id,
                'name'       => $item->product->name ?? null,
                'quantity'   => $item->quantity,
                'unitPrice'  => [
                    'amount'   => $item->unit_price_amount,
                    'currency' => $item->unit_price_currency,
                ],
                'totalPrice' => [
                    'amount'   => $lineTotal,
                    'currency' => $item->unit_price_currency,
                ],
            ];
        })->values()->all();

        $totalAmount = $order->items->sum(static fn($item) => $item->quantity * $item->unit_price_amount);
        $currency = $order->items->first()?->unit_price_currency ?? 'BRL';

        return [
            'id'          => $order->id,
            'orderNumber' => $order->order_number,
            'status'      => $order->status,
            'userId'    => $order->user_id,
            'items'     => $items,
            'total'     => ['amount' => $totalAmount, 'currency' => $currency],
            'createdAt' => $order->created_at?->toISOString(),
            'updatedAt' => $order->updated_at?->toISOString(),
        ];
    }
}
