<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_product(): void
    {
        Sanctum::actingAs(User::factory()->owner()->create());

        $this->postJson('/api/v1/products', [
            'name' => 'Kopi Baru',
            'price' => 10000,
            'stock' => 50,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Kopi Baru');
    }

    public function test_kasir_cannot_create_product(): void
    {
        Sanctum::actingAs(User::factory()->kasir()->create());

        $this->postJson('/api/v1/products', [
            'name' => 'Kopi',
            'price' => 10000,
            'stock' => 50,
        ])->assertStatus(403);
    }

    public function test_kasir_can_read_active_products(): void
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        Sanctum::actingAs(User::factory()->kasir()->create());

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_owner_sees_inactive_products_when_requested(): void
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        Sanctum::actingAs(User::factory()->owner()->create());

        $this->getJson('/api/v1/products?include_inactive=1')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_delete_soft_deactivates(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        Sanctum::actingAs(User::factory()->owner()->create());

        $this->deleteJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_low_stock_returns_correct_products(): void
    {
        Product::factory()->create(['stock' => 3, 'min_stock' => 5]);
        Product::factory()->create(['stock' => 50, 'min_stock' => 5]);

        Sanctum::actingAs(User::factory()->owner()->create());

        $this->getJson('/api/v1/products/low-stock')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
