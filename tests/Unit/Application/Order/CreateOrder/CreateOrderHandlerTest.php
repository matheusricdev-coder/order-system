<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Order\CreateOrder;

use App\Application\Common\DomainEventBus;
use App\Application\Common\TransactionManager;
use App\Application\Order\CreateOrder\CreateOrderCommand;
use App\Application\Order\CreateOrder\CreateOrderHandler;
use App\Application\Repositories\Order\OrderRepository;
use App\Application\Repositories\Product\ProductRepository;
use App\Application\Repositories\Stock\StockRepository;
use App\Application\Repositories\User\UserRepository;
use App\Common\IdGenerator;
use App\Domain\Common\Money;
use App\Domain\Order\Order;
use App\Domain\Product\Product;
use App\Domain\Stock\Stock;
use App\Domain\User\User;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CreateOrderHandlerTest extends TestCase
{
    public function test_it_creates_order_inside_transaction_and_locks_stock(): void
    {
        $transactionManager = new TransactionManagerSpy();
        $domainEventBus = new DomainEventBusSpy();
        $userRepository = new InMemoryUserRepository(new User('u-1', true));
        $productRepository = new InMemoryProductRepository(
            new Product('p-1', 'Notebook', new Money(5000, 'BRL'), 'c-1', 'co-1')
        );
        $stockRepository = new InMemoryStockRepository(new Stock('s-1', 'p-1', 10));
        $orderRepository = new InMemoryOrderRepository();
        $idGenerator = new SequenceIdGenerator(['o-1', 'i-1']);

        $handler = new CreateOrderHandler(
            $userRepository,
            $productRepository,
            $stockRepository,
            $orderRepository,
            $idGenerator,
            $transactionManager,
            $domainEventBus,
        );

        $dto = $handler->handle(new CreateOrderCommand('u-1', [['productId' => 'p-1', 'quantity' => 2]]));

        self::assertSame(1, $transactionManager->runCalls);
        self::assertSame(['p-1'], $stockRepository->forUpdateLookups);
        self::assertNotNull($orderRepository->savedOrder);
        self::assertSame('o-1', $dto->id);
        self::assertCount(1, $dto->items);
        self::assertCount(1, $domainEventBus->publishedEvents);
    }

    public function test_it_rejects_inactive_user(): void
    {
        $handler = new CreateOrderHandler(
            new InMemoryUserRepository(new User('u-1', false)),
            new InMemoryProductRepository(new Product('p-1', 'Notebook', new Money(5000, 'BRL'), 'c-1', 'co-1')),
            new InMemoryStockRepository(new Stock('s-1', 'p-1', 10)),
            new InMemoryOrderRepository(),
            new SequenceIdGenerator(['o-1', 'i-1']),
            new TransactionManagerSpy(),
            new DomainEventBusSpy(),
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Inactive user cannot create orders');

        $handler->handle(new CreateOrderCommand('u-1', [['productId' => 'p-1', 'quantity' => 1]]));
    }
}

final class TransactionManagerSpy implements TransactionManager
{
    public int $runCalls = 0;

    public function run(callable $fn): mixed
    {
        $this->runCalls++;

        return $fn();
    }
}

final class DomainEventBusSpy implements DomainEventBus
{
    /** @var object[] */
    public array $publishedEvents = [];

    public function publish(array $events): void
    {
        $this->publishedEvents = [...$this->publishedEvents, ...$events];
    }
}

final class SequenceIdGenerator implements IdGenerator
{
    /** @var string[] */
    private array $ids;

    /** @param string[] $ids */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function generate(): string
    {
        return array_shift($this->ids) ?? 'fallback-id';
    }
}

final class InMemoryUserRepository implements UserRepository
{
    public function __construct(private User $user)
    {
    }

    public function findById(string $id): User
    {
        return $this->user;
    }
}

final class InMemoryProductRepository implements ProductRepository
{
    public function __construct(private Product $product)
    {
    }

    public function findById(string $id): Product
    {
        return $this->product;
    }
}

final class InMemoryStockRepository implements StockRepository
{
    /** @var string[] */
    public array $forUpdateLookups = [];

    public function __construct(private Stock $stock)
    {
    }

    public function findByProductId(string $productId): Stock
    {
        throw new DomainException('findByProductId should not be used in write use case');
    }

    public function findByProductIdForUpdate(string $productId): Stock
    {
        $this->forUpdateLookups[] = $productId;

        return $this->stock;
    }

    public function save(Stock $stock): void
    {
        $this->stock = $stock;
    }
}

final class InMemoryOrderRepository implements OrderRepository
{
    public ?Order $savedOrder = null;

    public function save(Order $order): void
    {
        $this->savedOrder = $order;
    }

    public function findById(string $id): Order
    {
        throw new DomainException('Not used in this test');
    }

    public function findByIdForUpdate(string $id): Order
    {
        throw new DomainException('Not used in this test');
    }

    public function findByPaymentIntentId(string $intentId): Order
    {
        throw new DomainException('Not used in this test');
    }
}
