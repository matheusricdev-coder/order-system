<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Application\Payment\PaymentGateway;
use App\Application\Payment\PaymentIntentResult;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\StockModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PayOrderEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $mock = $this->createMock(PaymentGateway::class);
        $mock->method('createIntent')->willReturn(
            new PaymentIntentResult(
                intentId:     'pi_test_fake_intent',
                clientSecret: 'pi_test_fake_secret',
            )
        );
        $this->app->instance(PaymentGateway::class, $mock);
    }

    public function test_it_pays_order_and_returns_payload(): void
    {
        $userId = (string) str()->uuid();
        $this->seedUser($userId);
        [$orderId] = $this->seedCreatedOrder(userId: $userId);

        $response = $this->withBearerToken($userId)
            ->postJson("/api/v1/orders/{$orderId}/pay");

        $response
            ->assertOk()
            ->assertHeader('X-Correlation-Id')
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.status', 'payment_pending');
    }

    public function test_it_returns_401_without_auth_token(): void
    {
        $this->postJson('/api/v1/orders/' . str()->uuid() . '/pay')
            ->assertStatus(401);
    }

    public function test_it_returns_404_when_order_does_not_exist(): void
    {
        $userId = (string) str()->uuid();
        $this->seedUser($userId);

        $this->withBearerToken($userId)
            ->postJson('/api/v1/orders/' . str()->uuid() . '/pay')
            ->assertStatus(404)
            ->assertJsonPath('error.message', 'Order not found');
    }

    public function test_it_returns_403_when_requester_is_not_owner(): void
    {
        $ownerId     = (string) str()->uuid();
        $requesterId = (string) str()->uuid();
        $this->seedUser($requesterId);
        [$orderId] = $this->seedCreatedOrder(userId: $ownerId);

        $this->withBearerToken($requesterId)
            ->postJson("/api/v1/orders/{$orderId}/pay")
            ->assertStatus(403);
    }

    public function test_it_returns_409_when_order_cannot_be_paid(): void
    {
        $userId = (string) str()->uuid();
        $this->seedUser($userId);
        [$orderId] = $this->seedCreatedOrder(userId: $userId, status: 'paid');

        $this->withBearerToken($userId)
            ->postJson("/api/v1/orders/{$orderId}/pay")
            ->assertStatus(409)
            ->assertJsonPath('error.message', 'Order cannot be paid');
    }

    private function seedUser(string $userId): void
    {
        UserModel::query()->firstOrCreate(
            ['id' => $userId],
            [
                'name'       => 'Test',
                'surname'    => 'User',
                'birth_date' => '1990-01-01',
                'password'   => 'secret',
                'active'     => true,
            ],
        );
    }

    private function seedCreatedOrder(string $userId, string $status = 'created'): array
    {
        $orderId   = (string) str()->uuid();
        $productId = (string) str()->uuid();

        $this->seedUser($userId);

        ProductModel::query()->create([
            'id'             => $productId,
            'name'           => 'Teclado',
            'price_amount'   => 1000,
            'price_currency' => 'BRL',
            'category_id'    => $this->seedCategory(),
            'company_id'     => $this->seedCompany(),
        ]);

        OrderModel::query()->create([
            'id'      => $orderId,
            'user_id' => $userId,
            'status'  => $status,
        ]);

        OrderItemModel::query()->create([
            'id'                  => (string) str()->uuid(),
            'order_id'            => $orderId,
            'product_id'          => $productId,
            'quantity'            => 1,
            'unit_price_amount'   => 1000,
            'unit_price_currency' => 'BRL',
        ]);

        StockModel::query()->create([
            'id'                => (string) str()->uuid(),
            'product_id'        => $productId,
            'quantity_total'    => 10,
            'quantity_reserved' => 1,
        ]);

        return [$orderId, $productId];
    }

    private function withBearerToken(string $userId): static
    {
        $token = UserModel::query()->findOrFail($userId)->createToken('test-token')->plainTextToken;

        return $this->withHeaders(['Authorization' => 'Bearer ' . $token]);
    }
}
