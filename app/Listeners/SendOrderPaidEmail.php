<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Application\Order\DTO\OrderDTO;
use App\Application\Repositories\Order\OrderRepository;
use App\Domain\Order\Events\OrderPaid;
use App\Mail\OrderPaidMail;
use App\Models\UserModel;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class SendOrderPaidEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function __construct(
        private readonly MailerContract $mailer,
        private readonly OrderRepository $orderRepository,
    ) {}

    public function handle(OrderPaid $event): void
    {
        $user = UserModel::query()->find($event->userId);

        if ($user === null) {
            Log::warning("SendOrderPaidEmail: user [{$event->userId}] not found, skipping.");
            return;
        }

        $order    = $this->orderRepository->findById($event->orderId);
        $orderDto = OrderDTO::fromDomain($order);

        $this->mailer->to($user->email)->send(new OrderPaidMail($orderDto));

        Log::info("OrderPaidMail sent to [{$user->email}] for order [{$event->orderId}]");
    }

    public function failed(OrderPaid $event, \Throwable $exception): void
    {
        Log::error("SendOrderPaidEmail failed for order [{$event->orderId}]: {$exception->getMessage()}");
    }
}
