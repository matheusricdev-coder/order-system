<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Order;

use App\Domain\Common\Money;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Order;
use App\Domain\Order\OrderItem;
use App\Domain\Order\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderDomainEventsTest extends TestCase
{
    public function test_order_records_created_event(): void
    {
        $order = new Order('o-1', 'u-1');
        $order->addItem(new OrderItem('i-1', 'p-1', 2, new Money(1000, 'BRL')));
        $order->recordCreated();

        $events = $order->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(OrderCreated::class, $events[0]);

        /** @var OrderCreated $event */
        $event = $events[0];
        self::assertSame('o-1', $event->orderId);
        self::assertSame('u-1', $event->userId);
        self::assertSame(2000, $event->totalAmountInCents);
        self::assertSame('BRL', $event->currency);
    }

    public function test_mark_as_paid_records_order_paid_event(): void
    {
        $order = Order::reconstitute('o-1', 'u-1', OrderStatus::PAYMENT_PENDING, []);
        $order->markAsPaid();

        $events = $order->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(OrderPaid::class, $events[0]);
        self::assertSame('o-1', $events[0]->orderId);
    }

    public function test_mark_as_cancelled_records_order_cancelled_event(): void
    {
        $order = new Order('o-1', 'u-1');
        $order->markAsCancelled();

        $events = $order->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(OrderCancelled::class, $events[0]);
        self::assertSame('o-1', $events[0]->orderId);
    }

    public function test_pull_domain_events_clears_pending_events(): void
    {
        $order = Order::reconstitute('o-1', 'u-1', OrderStatus::PAYMENT_PENDING, []);
        $order->markAsPaid();

        $order->pullDomainEvents(); // consume events
        $remaining = $order->pullDomainEvents();

        self::assertEmpty($remaining);
    }

    public function test_reconstitute_does_not_record_events(): void
    {
        $order = Order::reconstitute('o-1', 'u-1', OrderStatus::PAID, []);

        self::assertEmpty($order->pullDomainEvents());
    }
}
