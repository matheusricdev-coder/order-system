<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Application\Order\DTO\OrderDTO;
use App\Application\Repositories\Order\OrderRepository;
use App\Domain\Order\Events\OrderCancelled;
use App\Mail\OrderCancelledMail;
use App\Models\UserModel;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class SendOrderCancelledEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function __construct(
        private readonly MailerContract $mailer,
        private readonly OrderRepository $orderRepository,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        $user = UserModel::query()->find($event->userId);

        if ($user === null) {
            Log::warning("SendOrderCancelledEmail: user [{$event->userId}] not found, skipping.");
            return;
        }

        $order    = $this->orderRepository->findById($event->orderId);
        $orderDto = OrderDTO::fromDomain($order);

        $this->mailer->to($user->email)->send(new OrderCancelledMail($orderDto));

        Log::info("OrderCancelledMail sent to [{$user->email}] for order [{$event->orderId}]");
    }

    public function failed(OrderCancelled $event, \Throwable $exception): void
    {
        Log::error("SendOrderCancelledEmail failed for order [{$event->orderId}]: {$exception->getMessage()}");
    }
}
