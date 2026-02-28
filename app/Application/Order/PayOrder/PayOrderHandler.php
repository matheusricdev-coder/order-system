<?php

declare(strict_types=1);

namespace App\Application\Order\PayOrder;

use App\Application\Common\TransactionManager;
use App\Application\Order\DTO\OrderDTO;
use App\Application\Payment\PaymentGateway;
use App\Application\Payment\PaymentIntentResult;
use App\Application\Repositories\Order\OrderRepository;
use App\Domain\Order\Exceptions\InvalidOrderTransitionException;
use App\Domain\Order\Exceptions\UnauthorizedOrderException;

/**
 * Initiates the Stripe payment flow for a given order.
 *
 * Flow:
 *   1. Verify ownership.
 *   2. Create a Stripe PaymentIntent for the order total (outside transaction —
 *      it's an external HTTP call that must not hold a DB lock).
 *   3. Inside a transaction, attach the intent ID to the order and transition
 *      to PAYMENT_PENDING.
 *
 * Stock is NOT consumed here. Consumption happens asynchronously when Stripe
 * fires the payment_intent.succeeded webhook → FinalizePaymentHandler.
 *
 * If the DB transaction fails after the Stripe intent is created, the intent
 * is simply abandoned and will expire automatically after 24 h.
 */
final class PayOrderHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly PaymentGateway $paymentGateway,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function handle(PayOrderCommand $command): OrderDTO
    {
        // Step 1 — load order and verify ownership (outside transaction; read-only)
        $order = $this->orderRepository->findById($command->orderId);

        if (!$order->ownedBy($command->requesterId)) {
            throw UnauthorizedOrderException::notOwner($command->orderId);
        }
        if (!$order->canInitiatePayment()) {
            throw InvalidOrderTransitionException::cannotBePaid($order->status()->value);
        }

        // Step 2 — create PaymentIntent (external call, no DB lock held)
        $intentResult = $this->paymentGateway->createIntent(
            orderId: $order->id(),
            amount:  $order->totalPrice(),
        );

        // Step 3 — persist the intent ID and transition status atomically
        $this->transactionManager->run(function () use ($command, $intentResult): void {
            $order = $this->orderRepository->findByIdForUpdate($command->orderId);

            if (!$order->ownedBy($command->requesterId)) {
                throw UnauthorizedOrderException::notOwner($command->orderId);
            }

            $order->attachPaymentIntent($intentResult->intentId);
            $this->orderRepository->save($order);
        });

        // Reload the (now PAYMENT_PENDING) order to build the DTO
        $order = $this->orderRepository->findById($command->orderId);
        $dto   = OrderDTO::fromDomain($order);

        // Attach client_secret for the frontend — never persisted server-side
        return new OrderDTO(
            id:                 $dto->id,
            userId:             $dto->userId,
            status:             $dto->status,
            totalAmountInCents: $dto->totalAmountInCents,
            currency:           $dto->currency,
            items:              $dto->items,
            paymentIntentId:    $dto->paymentIntentId,
            clientSecret:       $intentResult->clientSecret,
        );
    }
}
