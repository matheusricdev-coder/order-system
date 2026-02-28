<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\ProductModel;
use App\Models\StockModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class CreateOrderEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_it_creates_order_and_returns_201_payload(): void
    {
        $userId    = (string) str()->uuid();
        $productId = (string) str()->uuid();

        UserModel::query()->create([
            'id'         => $userId,
            'name'       => 'Ana',
            'surname'    => 'Silva',
            'birth_date' => '1990-01-01',
            'password'   => 'secret',
            'active'     => true,
        ]);

        ProductModel::query()->create([
            'id'             => $productId,
            'name'           => 'Teclado',
            'price_amount'   => 1000,
            'price_currency' => 'BRL',
            'category_id'    => $this->seedCategory(),
            'company_id'     => $this->seedCompany(),
        ]);

        StockModel::query()->create([
            'id'                => (string) str()->uuid(),
            'product_id'        => $productId,
            'quantity_total'    => 10,
            'quantity_reserved' => 0,
        ]);

        $response = $this->withBearerToken($userId)
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['productId' => $productId, 'quantity' => 2],
                ],
            ]);

        $response
            ->assertCreated()
            ->assertHeader('X-Correlation-Id')
            ->assertJsonPath('data.status', 'created')
            ->assertJsonPath('data.total.amount', 2000)
            ->assertJsonPath('data.total.currency', 'BRL')
            ->assertJsonPath('data.items.0.productId', $productId)
            ->assertJsonPath('data.items.0.quantity', 2);
    }

    public function test_it_returns_401_without_auth_token(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'items' => [
                ['productId' => (string) str()->uuid(), 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_it_returns_422_when_payload_is_invalid(): void
    {
        $userId = (string) str()->uuid();

        UserModel::query()->create([
            'id'         => $userId,
            'name'       => 'Ana',
            'surname'    => 'Silva',
            'birth_date' => '1990-01-01',
            'password'   => 'secret',
            'active'     => true,
        ]);

        $response = $this->withBearerToken($userId)
            ->postJson('/api/v1/orders', ['items' => []]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_it_returns_409_on_insufficient_stock(): void
    {
        $userId    = (string) str()->uuid();
        $productId = (string) str()->uuid();

        UserModel::query()->create([
            'id'         => $userId,
            'name'       => 'Ana',
            'surname'    => 'Silva',
            'birth_date' => '1990-01-01',
            'password'   => 'secret',
            'active'     => true,
        ]);

        ProductModel::query()->create([
            'id'             => $productId,
            'name'           => 'Teclado',
            'price_amount'   => 1000,
            'price_currency' => 'BRL',
            'category_id'    => $this->seedCategory(),
            'company_id'     => $this->seedCompany(),
        ]);

        StockModel::query()->create([
            'id'                => (string) str()->uuid(),
            'product_id'        => $productId,
            'quantity_total'    => 1,
            'quantity_reserved' => 0,
        ]);

        $response = $this->withBearerToken($userId)
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['productId' => $productId, 'quantity' => 2],
                ],
            ]);

        $response
            ->assertStatus(409)
            ->assertJsonPath('error.message', 'Insufficient stock');
    }

    public function test_it_returns_404_when_product_does_not_exist(): void
    {
        $userId = (string) str()->uuid();

        UserModel::query()->create([
            'id'         => $userId,
            'name'       => 'Ana',
            'surname'    => 'Silva',
            'birth_date' => '1990-01-01',
            'password'   => 'secret',
            'active'     => true,
        ]);

        $response = $this->withBearerToken($userId)
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['productId' => (string) str()->uuid(), 'quantity' => 1],
                ],
            ]);

        $response
            ->assertStatus(404);
    }

    private function withBearerToken(string $userId): static
    {
        $token = UserModel::query()->findOrFail($userId)->createToken('test-token')->plainTextToken;

        return $this->withHeaders(['Authorization' => 'Bearer ' . $token]);
    }
}
