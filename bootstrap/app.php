<?php

use App\Domain\Order\Exceptions\InvalidOrderTransitionException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Exceptions\UnauthorizedOrderException;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Stock\Exceptions\InsufficientStockException;
use App\Domain\Stock\Exceptions\StockNotFoundException;
use App\Domain\User\Exceptions\InactiveUserException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\RequireAdminRole;
use App\Http\Middleware\ValidateApiKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(CorrelationIdMiddleware::class);
        $middleware->alias([
            'admin'   => RequireAdminRole::class,
            'api.key' => ValidateApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request): ?JsonResponse {
            if (!$request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => [
                        'code'           => 'VALIDATION_ERROR',
                        'message'        => 'Validation failed',
                        'correlation_id' => $request->attributes->get(CorrelationIdMiddleware::ATTRIBUTE),
                        'details'        => $e->errors(),
                    ],
                ], 422);
            }

            $status = match (true) {
                $e instanceof OrderNotFoundException,
                $e instanceof UserNotFoundException,
                $e instanceof ProductNotFoundException,
                $e instanceof StockNotFoundException  => 404,

                $e instanceof UnauthorizedOrderException => 403,
                $e instanceof InactiveUserException      => 422,                $e instanceof AuthenticationException    => 401,                $e instanceof \DomainException           => 409,
                $e instanceof HttpExceptionInterface     => $e->getStatusCode(),
                default                                  => 500,
            };

            if ($status < 400) {
                return null;
            }

            /** @var array<class-string<\Throwable>, string> */
            $codeMap = [
                OrderNotFoundException::class          => 'ORDER_NOT_FOUND',
                UserNotFoundException::class           => 'USER_NOT_FOUND',
                ProductNotFoundException::class        => 'PRODUCT_NOT_FOUND',
                StockNotFoundException::class          => 'STOCK_NOT_FOUND',
                UnauthorizedOrderException::class      => 'ORDER_ACCESS_DENIED',
                InactiveUserException::class           => 'USER_INACTIVE',
                InvalidOrderTransitionException::class => 'INVALID_ORDER_TRANSITION',
                InsufficientStockException::class      => 'INSUFFICIENT_STOCK',
                AuthenticationException::class         => 'UNAUTHENTICATED',
            ];

            $code    = $codeMap[$e::class] ?? 'INTERNAL_ERROR';
            $message = ($status < 500) ? $e->getMessage() : 'An unexpected error occurred';

            return response()->json([
                'error' => [
                    'code'           => $code,
                    'message'        => $message,
                    'correlation_id' => $request->attributes->get(CorrelationIdMiddleware::ATTRIBUTE),
                ],
            ], $status);
        });
    })->create();
