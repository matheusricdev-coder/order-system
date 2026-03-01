<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Order\CancelOrder\CancelOrderCommand;
use App\Application\Order\CancelOrder\CancelOrderHandler;
use App\Application\Order\CreateOrder\CreateOrderCommand;
use App\Application\Order\CreateOrder\CreateOrderHandler;
use App\Application\Order\GetOrder\GetOrderHandler;
use App\Application\Order\GetOrder\GetOrderQuery;
use App\Application\Order\ListOrders\ListOrdersHandler;
use App\Application\Order\ListOrders\ListOrdersQuery;
use App\Application\Order\PayOrder\PayOrderCommand;
use App\Application\Order\PayOrder\PayOrderHandler;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Requests\CreateOrderRequest;
use App\Models\OrderModel;
use App\Models\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class OrderController extends Controller
{
    public function create(
        CreateOrderRequest $request,
        CreateOrderHandler $handler,
    ): JsonResponse {
        $authUser = $this->authUser($request);
        $payload  = $request->validated();

        $dto = $handler->handle(new CreateOrderCommand(
            userId: $authUser->id,
            items: $payload['items'],
        ));

        $orderNumber = (int) OrderModel::query()->where('id', $dto->id)->value('order_number');

        return response()->json(
            [
                'data' => array_merge($dto->toArray(), ['orderNumber' => $orderNumber]),
                'meta' => ['correlationId' => $this->correlationId($request)],
            ],
            201,
        );
    }

    public function pay(
        string $orderNumber,
        Request $request,
        PayOrderHandler $handler,
    ): JsonResponse {
        $authUser = $this->authUser($request);
        $orderId  = $this->resolveOrderById($orderNumber);

        $dto = $handler->handle(new PayOrderCommand(
            orderId: $orderId,
            requesterId: $authUser->id,
        ));

        return response()->json([
            'data' => $dto->toArray(),
            'meta' => ['correlationId' => $this->correlationId($request)],
        ]);
    }

    public function cancel(
        string $orderNumber,
        Request $request,
        CancelOrderHandler $handler,
    ): JsonResponse {
        $authUser = $this->authUser($request);
        $orderId  = $this->resolveOrderById($orderNumber);

        $dto = $handler->handle(new CancelOrderCommand(
            orderId: $orderId,
            requesterId: $authUser->id,
        ));

        return response()->json([
            'data' => $dto->toArray(),
            'meta' => ['correlationId' => $this->correlationId($request)],
        ]);
    }

    public function show(
        string $orderNumber,
        Request $request,
        GetOrderHandler $handler,
    ): JsonResponse {
        $authUser = $this->authUser($request);
        $orderId  = $this->resolveOrderById($orderNumber);

        return response()->json([
            'data' => $handler->handle(new GetOrderQuery(
                orderId: $orderId,
                requesterId: $authUser->id,
            )),
            'meta' => ['correlationId' => $this->correlationId($request)],
        ]);
    }

    /** @var list<string> */
    private const ALLOWED_STATUSES = ['created', 'paid', 'cancelled'];

    private const PER_PAGE_MAX = 100;
    private const PER_PAGE_DEFAULT = 15;

    public function index(
        Request $request,
        ListOrdersHandler $handler,
    ): JsonResponse {
        $request->validate([
            'status'  => ['nullable', Rule::in(self::ALLOWED_STATUSES)],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
            'page'    => ['nullable', 'integer', 'min:1'],
        ]);

        $authUser = $this->authUser($request);
        $perPage  = min((int) $request->query('perPage', self::PER_PAGE_DEFAULT), self::PER_PAGE_MAX);

        $result = $handler->handle(new ListOrdersQuery(
            requesterId: $authUser->id,
            status: $request->query('status'),
            perPage: $perPage,
            page: (int) $request->query('page', 1),
        ));

        return response()->json([
            'data' => $result['data'],
            'meta' => [
                'total'         => $result['total'],
                'perPage'       => $result['per_page'],
                'currentPage'   => $result['current_page'],
                'lastPage'      => $result['last_page'],
                'correlationId' => $this->correlationId($request),
            ],
        ]);
    }

    private function resolveOrderById(string $orderNumber): string
    {
        // The route parameter is the human-readable order_number (integer).
        // Resolve it to the internal UUID used by all handlers.
        $isUuid = (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $orderNumber,
        );

        $id = $isUuid
            ? OrderModel::query()->where('id', $orderNumber)->value('id')
            : OrderModel::query()->where('order_number', (int) $orderNumber)->value('id');

        if ($id === null) {
            throw OrderNotFoundException::withId($orderNumber);
        }

        return (string) $id;
    }

    private function authUser(Request $request): UserModel
    {
        /** @var UserModel $user */
        $user = $request->user();

        return $user;
    }

    private function correlationId(Request $request): ?string
    {
        return $request->attributes->get(CorrelationIdMiddleware::ATTRIBUTE);
    }
}
