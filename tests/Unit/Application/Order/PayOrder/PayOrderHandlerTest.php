<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Order\PayOrder;

use App\Application\Common\TransactionManager;
use App\Application\Order\PayOrder\PayOrderCommand;
use App\Application\Order\PayOrder\PayOrderHandler;
use App\Application\Payment\PaymentGateway;
use App\Application\Payment\PaymentIntentResult;
use App\Application\Repositories\Order\OrderRepository;
use App\Domain\Common\Money;
use App\Domain\Order\Order;
use App\Domain\Order\OrderItem;
use App\Domain\Order\OrderStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

final class PayOrderHandlerTest extends TestCase
{
    public function test_it_initiates_payment_inside_transaction_and_locks_rows(): void
    {
        $order = new Order('o-1', 'u-1');
        $order->addItem(new OrderItem('i-1', 'p-1', 2, new Money(1000, 'BRL')));

        $transactionManager = new TransactionManagerSpy();
        $orderRepository    = new InMemoryPayOrderRepository($order);
        $gatewayResult      = new PaymentIntentResult('pi_test_123', 'secret_123');
        $paymentGateway     = new FakePaymentGateway($gatewayResult);

        $handler = new PayOrderHandler($orderRepository, $paymentGateway, $transactionManager);

        $dto = $handler->handle(new PayOrderCommand(orderId: 'o-1', requesterId: 'u-1'));

        self::assertSame(1, $transactionManager->runCalls);
        self::assertSame(['o-1'], $orderRepository->forUpdateLookups);
        self::assertTrue($paymentGateway->createIntentCalled);
        self::assertSame('payment_pending', $dto->status);
        self::assertSame('secret_123', $dto->clientSecret);
    }

    public function test_it_rejects_order_that_cannot_initiate_payment(): void
    {
        $order = Order::reconstitute('o-1', 'u-1', OrderStatus::CANCELLED, []);

        $handler = new PayOrderHandler(
            new InMemoryPayOrderRepository($order),
            new FakePaymentGateway(new PaymentIntentResult('pi_x', 'secret_x')),
            new TransactionManagerSpy(),
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Order cannot be paid');

        $handler->handle(new PayOrderCommand(orderId: 'o-1', requesterId: 'u-1'));
    }

    public function test_it_rejects_when_requester_is_not_owner(): void
    {
        $order = new Order('o-1', 'u-1');
        $order->addItem(new OrderItem('i-1', 'p-1', 1, new Money(1000, 'BRL')));

        $handler = new PayOrderHandler(
            new InMemoryPayOrderRepository($order),
            new FakePaymentGateway(new PaymentIntentResult('pi_x', 'secret_x')),
            new TransactionManagerSpy(),
        );

        $this->expectException(DomainException::class);

        $handler->handle(new PayOrderCommand(orderId: 'o-1', requesterId: 'other-user'));
    }

    public function test_gateway_is_not_called_when_transition_is_invalid(): void
    {
        $order   = Order::reconstitute('o-1', 'u-1', OrderStatus::PAID, []);
        $gateway = new FakePaymentGateway(new PaymentIntentResult('pi_x', 'secret_x'));

        $handler = new PayOrderHandler(
            new InMemoryPayOrderRepository($order),
            $gateway,
            new TransactionManagerSpy(),
        );

        try {
            $handler->handle(new PayOrderCommand(orderId: 'o-1', requesterId: 'u-1'));
        } catch (DomainException) {
            // expected
        }

        self::assertFalse($gateway->createIntentCalled, 'Stripe must not be called when transition is invalid');
    }
}

// ──────────────────────────── Test doubles ────────────────────────────

final class TransactionManagerSpy implements TransactionManager
{
    public int $runCalls = 0;

    public function run(callable $fn): mixed
    {
        $this->runCalls++;
        return $fn();
    }
}

final class FakePaymentGateway implements PaymentGateway
{
    public bool $createIntentCalled = false;

    public function __construct(private readonly PaymentIntentResult $result) {}

    public function createIntent(string $orderId, Money $amount): PaymentIntentResult
    {
        $this->createIntentCalled = true;
        return $this->result;
    }
}

final class InMemoryPayOrderRepository implements OrderRepository
{
    /** @var string[] */
    public array $forUpdateLookups = [];

    public function __construct(private Order $order) {}

    public function save(Order $order): void
    {
        $this->order = $order;
    }

    public function findById(string $id): Order
    {
        return $this->order;
    }

    public function findByIdForUpdate(string $id): Order
    {
        $this->forUpdateLookups[] = $id;
        return $this->order;
    }

    public function findByPaymentIntentId(string $intentId): Order
    {
        throw new DomainException('findByPaymentIntentId not used in this test');
    }
}
