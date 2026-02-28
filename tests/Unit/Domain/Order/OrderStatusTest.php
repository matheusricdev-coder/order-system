<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderStatusTest extends TestCase
{
    public function test_created_can_transition_to_payment_pending(): void
    {
        self::assertTrue(OrderStatus::CREATED->canTransitionTo(OrderStatus::PAYMENT_PENDING));
    }

    public function test_created_cannot_transition_to_paid_directly(): void
    {
        self::assertFalse(OrderStatus::CREATED->canTransitionTo(OrderStatus::PAID));
    }

    public function test_payment_pending_can_transition_to_paid(): void
    {
        self::assertTrue(OrderStatus::PAYMENT_PENDING->canTransitionTo(OrderStatus::PAID));
    }

    public function test_payment_pending_can_transition_to_cancelled(): void
    {
        self::assertTrue(OrderStatus::PAYMENT_PENDING->canTransitionTo(OrderStatus::CANCELLED));
    }

    public function test_created_can_transition_to_cancelled(): void
    {
        self::assertTrue(OrderStatus::CREATED->canTransitionTo(OrderStatus::CANCELLED));
    }

    public function test_created_cannot_transition_to_itself(): void
    {
        self::assertFalse(OrderStatus::CREATED->canTransitionTo(OrderStatus::CREATED));
    }

    public function test_paid_cannot_transition_to_any_state(): void
    {
        self::assertFalse(OrderStatus::PAID->canTransitionTo(OrderStatus::CREATED));
        self::assertFalse(OrderStatus::PAID->canTransitionTo(OrderStatus::PAID));
        self::assertFalse(OrderStatus::PAID->canTransitionTo(OrderStatus::CANCELLED));
    }

    public function test_cancelled_cannot_transition_to_any_state(): void
    {
        self::assertFalse(OrderStatus::CANCELLED->canTransitionTo(OrderStatus::CREATED));
        self::assertFalse(OrderStatus::CANCELLED->canTransitionTo(OrderStatus::PAID));
        self::assertFalse(OrderStatus::CANCELLED->canTransitionTo(OrderStatus::CANCELLED));
    }
}
