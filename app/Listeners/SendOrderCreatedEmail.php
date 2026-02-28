<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Application\Order\DTO\OrderDTO;
use App\Application\Repositories\Order\OrderRepository;
use App\Domain\Order\Events\OrderCreated;
use App\Mail\OrderCreatedMail;
use App\Models\UserModel;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class SendOrderCreatedEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function __construct(
        private readonly MailerContract $mailer,
        private readonly OrderRepository $orderRepository,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $user = UserModel::query()->find($event->userId);

        if ($user === null) {
            Log::warning("SendOrderCreatedEmail: user [{$event->userId}] not found, skipping.");
            return;
        }

        $order    = $this->orderRepository->findById($event->orderId);
        $orderDto = OrderDTO::fromDomain($order);

        $this->mailer->to($user->email)->send(new OrderCreatedMail($orderDto));

        Log::info("OrderCreatedMail sent to [{$user->email}] for order [{$event->orderId}]");
    }

    /** Prevent listener from halting the queue on transient failures. */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error("SendOrderCreatedEmail failed for order [{$event->orderId}]: {$exception->getMessage()}");
    }
}
