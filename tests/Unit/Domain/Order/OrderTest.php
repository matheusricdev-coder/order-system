<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Order;

use App\Domain\Common\Money;
use App\Domain\Order\Order;
use App\Domain\Order\OrderItem;
use App\Domain\Order\OrderStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function test_status_starts_as_created(): void
    {
        $order = new Order(id: 'o1', userId: 'u1');

        self::assertSame(OrderStatus::CREATED, $order->status());
    }

    public function test_add_item_accepts_positive_quantity(): void
    {
        $order = new Order(id: 'o1', userId: 'u1');

        $order->addItem(
            new OrderItem(id: 'i1', productId: 'p1', quantity: 1, unitPrice: new Money(1000, 'BRL'))
        );

        self::assertCount(1, $order->items());
    }

    public function test_add_item_rejects_zero_or_negative_quantity(): void
    {
        $order = new Order(id: 'o1', userId: 'u1');

        $this->expectException(DomainException::class);

        $order->addItem(
            new OrderItem(id: 'i1', productId: 'p1', quantity: 0, unitPrice: new Money(1000, 'BRL'))
        );
    }

    public function test_total_price_requires_at_least_one_item(): void
    {
        $order = new Order(id: 'o1', userId: 'u1');

        $this->expectException(DomainException::class);

        $order->totalPrice();
    }

    public function test_total_price_sums_item_totals(): void
    {
        $order = new Order(id: 'o1', userId: 'u1');

        $order->addItem(new OrderItem(id: 'i1', productId: 'p1', quantity: 2, unitPrice: new Money(1000, 'BRL'))); // 2000
        $order->addItem(new OrderItem(id: 'i2', productId: 'p2', quantity: 1, unitPrice: new Money(500, 'BRL')));  // 500

        $total = $order->totalPrice();

        self::assertSame(2500, $total->amount());
        self::assertSame('BRL', $total->currency());
    }

    public function test_can_be_paid_only_when_payment_pending(): void
    {
        $created = new Order(id: 'o1', userId: 'u1');
        self::assertFalse($created->canBePaid(), 'CREATED cannot go directly to PAID');

        $pending = Order::reconstitute(id: 'o1', userId: 'u1', status: OrderStatus::PAYMENT_PENDING, items: []);
        self::assertTrue($pending->canBePaid(), 'PAYMENT_PENDING can transition to PAID');

        $paid = Order::reconstitute(id: 'o1', userId: 'u1', status: OrderStatus::PAID, items: []);
        self::assertFalse($paid->canBePaid(), 'PAID is terminal');
    }

    public function test_mark_as_paid_changes_status(): void
    {
        $order = Order::reconstitute(id: 'o1', userId: 'u1', status: OrderStatus::PAYMENT_PENDING, items: []);

        $order->markAsPaid();

        self::assertSame(OrderStatus::PAID, $order->status());
    }

    public function test_it_cannot_be_paid_twice(): void
    {
        $order = Order::reconstitute(id: 'o1', userId: 'u1', status: OrderStatus::PAYMENT_PENDING, items: []);
        $order->markAsPaid();

        $this->expectException(DomainException::class);

        $order->markAsPaid();
    }

    public function test_can_be_cancelled_only_when_created(): void
    {
        $order = new Order(id: 'o1', userId: 'u1');

        self::assertTrue($order->canBeCancelled());

        $order->markAsCancelled();

        self::assertFalse($order->canBeCancelled());
    }

    public function test_mark_as_cancelled_changes_status(): void
    {
        $order = new Order(id: 'o1', userId: 'u1');

        $order->markAsCancelled();

        self::assertSame(OrderStatus::CANCELLED, $order->status());
    }

    public function test_it_cannot_be_cancelled_after_paid(): void
    {
        $order = Order::reconstitute(id: 'o1', userId: 'u1', status: OrderStatus::PAID, items: []);

        $this->expectException(DomainException::class);

        $order->markAsCancelled();
    }
}
