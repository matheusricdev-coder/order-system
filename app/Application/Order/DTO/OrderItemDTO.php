<?php

declare(strict_types=1);

namespace App\Application\Order\DTO;

use App\Domain\Order\OrderItem;

final class OrderItemDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity,
        public readonly int $unitPriceAmount,
        public readonly string $unitPriceCurrency,
        public readonly int $totalPriceAmount,
        public readonly ?string $name = null,
    ) {}

    public static function fromDomain(OrderItem $item): self
    {
        return new self(
            productId: $item->productId(),
            quantity: $item->quantity(),
            unitPriceAmount: $item->unitPrice()->amount(),
            unitPriceCurrency: $item->unitPrice()->currency(),
            totalPriceAmount: $item->totalPrice()->amount(),
        );
    }

    public function withName(string $name): self
    {
        return new self(
            productId:         $this->productId,
            quantity:          $this->quantity,
            unitPriceAmount:   $this->unitPriceAmount,
            unitPriceCurrency: $this->unitPriceCurrency,
            totalPriceAmount:  $this->totalPriceAmount,
            name:              $name,
        );
    }

    public function toArray(): array
    {
        return [
            'productId'  => $this->productId,
            'name'       => $this->name,
            'quantity'   => $this->quantity,
            'unitPrice'  => [
                'amount'   => $this->unitPriceAmount,
                'currency' => $this->unitPriceCurrency,
            ],
            'totalPrice' => [
                'amount'   => $this->totalPriceAmount,
                'currency' => $this->unitPriceCurrency,
            ],
        ];
    }
}
