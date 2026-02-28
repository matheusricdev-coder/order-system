<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Common\DomainEventBus;
use App\Application\Common\TransactionManager;
use App\Application\Repositories\Order\OrderRepository;
use App\Application\Repositories\Stock\StockRepository;
use App\Domain\Order\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cancels orders that have been stuck in `created` status for too long,
 * releasing the reserved stock back to the available pool.
 *
 * Orders stay in `created` when a user abandons the checkout flow before
 * visiting the payment page. Without this command the reserved stock would
 * be locked forever.
 *
 * Usage:
 *   php artisan orders:cancel-stale          # default: 30-minute TTL
 *   php artisan orders:cancel-stale --ttl=15  # custom TTL in minutes
 */
final class CancelStaleOrdersCommand extends Command
{
    protected $signature = 'orders:cancel-stale
        {--ttl=30 : Minutes a "created" order can remain open before being auto-cancelled}
        {--dry-run : Show what would be cancelled without making changes}';

    protected $description = 'Cancel abandoned orders (status=created) older than TTL and release their reserved stock';

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly StockRepository $stockRepository,
        private readonly TransactionManager $transactionManager,
        private readonly DomainEventBus $domainEventBus,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $ttl    = (int) $this->option('ttl');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subMinutes($ttl);

        $staleIds = DB::table('orders')
            ->where('status', 'created')
            ->where('created_at', '<', $cutoff)
            ->pluck('id');

        if ($staleIds->isEmpty()) {
            $this->info("No stale orders found (TTL={$ttl}min).");
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Found %d order(s) stuck in "created" older than %d min%s.',
            $staleIds->count(),
            $ttl,
            $dryRun ? ' [DRY RUN — no changes applied]' : '',
        ));

        $cancelled = 0;
        $errors    = 0;

        foreach ($staleIds as $orderId) {
            if ($dryRun) {
                $this->line("  [dry-run] Would cancel: {$orderId}");
                continue;
            }

            try {
                $order = $this->transactionManager->run(function () use ($orderId): Order {
                    $order = $this->orderRepository->findByIdForUpdate($orderId);

                    foreach ($order->items() as $item) {
                        $stock = $this->stockRepository->findByProductIdForUpdate($item->productId());
                        $stock->release($item->quantity());
                        $this->stockRepository->save($stock);
                    }

                    $order->markAsCancelled();
                    $this->orderRepository->save($order);

                    return $order;
                });

                $this->domainEventBus->publish($order->pullDomainEvents());

                $this->line("  Cancelled: {$orderId}");
                Log::info('[CancelStaleOrders] Order auto-cancelled', ['order_id' => $orderId, 'ttl_minutes' => $ttl]);
                $cancelled++;
            } catch (\Throwable $e) {
                $this->error("  Failed to cancel {$orderId}: {$e->getMessage()}");
                Log::error('[CancelStaleOrders] Failed to cancel order', [
                    'order_id' => $orderId,
                    'error'    => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        if (!$dryRun) {
            $this->info("Done. Cancelled: {$cancelled} | Errors: {$errors}");
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
