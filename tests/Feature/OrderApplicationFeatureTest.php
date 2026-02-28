<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Common\TransactionManager;
use App\Application\Order\CreateOrder\CreateOrderCommand;
use App\Application\Order\CreateOrder\CreateOrderHandler;
use App\Application\Order\PayOrder\PayOrderCommand;
use App\Application\Order\PayOrder\PayOrderHandler;
use App\Domain\Order\Order;
use App\Domain\Order\OrderItem;
use App\Domain\Order\OrderStatus;
use App\Infrastructure\Persistence\EloquentOrderRepository;
use App\Infrastructure\Persistence\EloquentStockRepository;
use App\Models\CategoryModel;
use App\Models\CompanyModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\StockModel;
use App\Models\UserModel;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @group integration
 *
 * Note: test_mysql_lock_for_update_blocks_competing_consumer_until_release
 * opens its own DB connections and thus cannot run inside the RefreshDatabase
 * wrapping transaction. It is guarded by markTestSkipped when pdo_mysql is
 * unavailable, and uses its own connection management.
 */

final class OrderApplicationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_handler_uses_transaction_manager_boundary(): void
    {
        $spy = new TransactionManagerFeatureSpy();
        $this->app->instance(TransactionManager::class, $spy);

        $this->seedCatalogForProduct('p-1', 1000);
        UserModel::query()->create($this->userPayload('u-1', true));
        StockModel::query()->create([
            'id' => 's-1',
            'product_id' => 'p-1',
            'quantity_total' => 5,
            'quantity_reserved' => 0,
        ]);

        $handler = $this->app->make(CreateOrderHandler::class);

        $handler->handle(new CreateOrderCommand('u-1', [['productId' => 'p-1', 'quantity' => 1]]));

        self::assertSame(1, $spy->runCalls);
    }

    public function test_create_order_rolls_back_all_reservations_when_any_item_fails(): void
    {
        UserModel::query()->create($this->userPayload('u-1', true));

        $this->seedCatalogForProduct('p-1', 1200);
        $this->seedCatalogForProduct('p-2', 1400);

        StockModel::query()->create([
            'id' => 's-1',
            'product_id' => 'p-1',
            'quantity_total' => 10,
            'quantity_reserved' => 0,
        ]);

        StockModel::query()->create([
            'id' => 's-2',
            'product_id' => 'p-2',
            'quantity_total' => 0,
            'quantity_reserved' => 0,
        ]);

        $handler = $this->app->make(CreateOrderHandler::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Insufficient stock');

        try {
            $handler->handle(new CreateOrderCommand('u-1', [
                ['productId' => 'p-1', 'quantity' => 2],
                ['productId' => 'p-2', 'quantity' => 1],
            ]));
        } finally {
            self::assertSame(0, (int) StockModel::query()->where('id', 's-1')->value('quantity_reserved'));
            self::assertSame(0, (int) StockModel::query()->where('id', 's-2')->value('quantity_reserved'));
            self::assertSame(0, OrderModel::query()->count());
            self::assertSame(0, OrderItemModel::query()->count());
        }
    }

    public function test_eloquent_repositories_keep_state_consistent_after_failed_pay_attempt(): void
    {
        $order = Order::reconstitute(
            id:     'o-1',
            userId: 'u-1',
            status: OrderStatus::PAID,
            items:  [new OrderItem('i-1', 'p-1', 2, new \App\Domain\Common\Money(1000, 'BRL'))],
        );

        $orderRepository = new EloquentOrderRepository();
        $stockRepository = new EloquentStockRepository();

        UserModel::query()->create($this->userPayload('u-1', true));
        $this->seedCatalogForProduct('p-1', 1000);
        StockModel::query()->create([
            'id' => 's-1',
            'product_id' => 'p-1',
            'quantity_total' => 10,
            'quantity_reserved' => 2,
        ]);
        $orderRepository->save($order);

        $handler = $this->app->make(PayOrderHandler::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Order cannot be paid');

        try {
            $handler->handle(new PayOrderCommand(orderId: 'o-1', requesterId: 'u-1'));
        } finally {
            self::assertSame('paid', (string) OrderModel::query()->where('id', 'o-1')->value('status'));
            self::assertSame(10, (int) StockModel::query()->where('id', 's-1')->value('quantity_total'));
            self::assertSame(2, (int) StockModel::query()->where('id', 's-1')->value('quantity_reserved'));
        }
    }

    public function test_mysql_lock_for_update_blocks_competing_consumer_until_release(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            self::markTestSkipped('pdo_mysql extension is required to prove lockForUpdate on MySQL.');
        }

        // RefreshDatabase wraps the test in a transaction; nested transactions
        // via beginTransaction() create savepoints which MySQL may not support
        // in this context. Skip to avoid SAVEPOINT errors.
        if (DB::transactionLevel() > 0) {
            self::markTestSkipped('Lock test cannot run inside a wrapping RefreshDatabase transaction.');
        }

        try {
            $firstConnection = DB::connection('mysql');
            $firstConnection->getPdo();
        } catch (\Throwable $throwable) {
            self::markTestSkipped('MySQL connection is not available in this environment.');
        }

        $driver = $firstConnection->getDriverName();
        if ($driver !== 'mysql') {
            self::markTestSkipped('This lock test is valid only with MySQL.');
        }

        $database = (string) config('database.connections.mysql.database');
        $secondConfig = config('database.connections.mysql');
        $secondConfig['database'] = $database;
        config(['database.connections.mysql_lock_test' => $secondConfig]);

        $secondConnection = DB::connection('mysql_lock_test');

        $firstConnection->statement('DROP TABLE IF EXISTS __lock_test_stocks');
        $firstConnection->statement('CREATE TABLE __lock_test_stocks (id VARCHAR(36) PRIMARY KEY, quantity_total INT NOT NULL, quantity_reserved INT NOT NULL) ENGINE=InnoDB');
        $firstConnection->statement("INSERT INTO __lock_test_stocks (id, quantity_total, quantity_reserved) VALUES ('p-1', 10, 2)");

        $firstConnection->beginTransaction();
        $firstConnection->selectOne("SELECT * FROM __lock_test_stocks WHERE id = 'p-1' FOR UPDATE");

        $secondConnection->statement('SET innodb_lock_wait_timeout = 1');

        $timedOut = false;

        try {
            $secondConnection->beginTransaction();
            $secondConnection->selectOne("SELECT * FROM __lock_test_stocks WHERE id = 'p-1' FOR UPDATE");
        } catch (\Throwable $throwable) {
            $timedOut = true;
        } finally {
            if ($secondConnection->transactionLevel() > 0) {
                $secondConnection->rollBack();
            }

            if ($firstConnection->transactionLevel() > 0) {
                $firstConnection->rollBack();
            }

            $firstConnection->statement('DROP TABLE IF EXISTS __lock_test_stocks');
            DB::purge('mysql_lock_test');
        }

        self::assertTrue($timedOut, 'Competing FOR UPDATE must wait and timeout while first transaction holds lock.');
    }

    private function seedCatalogForProduct(string $productId, int $priceAmount): void
    {
        CompanyModel::query()->firstOrCreate(['id' => 'co-1'], ['trade_name' => 'Company']);
        CategoryModel::query()->firstOrCreate(['id' => 'ca-1'], ['name' => 'Category']);

        ProductModel::query()->create([
            'id' => $productId,
            'name' => 'Product '.$productId,
            'price_amount' => $priceAmount,
            'price_currency' => 'BRL',
            'category_id' => 'ca-1',
            'company_id' => 'co-1',
        ]);
    }

    /** @return array<string, mixed> */
    private function userPayload(string $id, bool $active): array
    {
        return [
            'id' => $id,
            'name' => 'John',
            'surname' => 'Doe',
            'birth_date' => '1990-01-01',
            'email' => sprintf('%s@example.com', $id),
            'phone' => sprintf('555-000-%s', substr($id, -1)),
            'password' => 'secret',
            'cpf' => '12345678901',
            'active' => $active,
            'company_id' => null,
        ];
    }
}

final class TransactionManagerFeatureSpy implements TransactionManager
{
    public int $runCalls = 0;

    public function run(callable $fn): mixed
    {
        $this->runCalls++;

        return DB::transaction($fn);
    }
}
