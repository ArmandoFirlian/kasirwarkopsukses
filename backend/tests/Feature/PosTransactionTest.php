<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_create_pos_transaction(): void
    {
        $product = Product::factory()->create(['price' => 8000, 'stock' => 10]);
        Sanctum::actingAs(User::factory()->kasir()->create());

        $this->postJson('/api/v1/transactions', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'pay_amount' => 20000,
            'payment_method' => 'cash',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.total_price', 16000)
            ->assertJsonPath('data.change_amount', 4000)
            ->assertJsonPath('data.source', 'pos')
            ->assertJsonPath('data.status', 'completed');

        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_insufficient_payment_is_rejected(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        Sanctum::actingAs(User::factory()->kasir()->create());

        $this->postJson('/api/v1/transactions', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'pay_amount' => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(422);

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(0, Transaction::count());
    }

    public function test_owner_cannot_create_pos_transaction(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        Sanctum::actingAs(User::factory()->owner()->create());

        $this->postJson('/api/v1/transactions', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'pay_amount' => 99999,
            'payment_method' => 'cash',
        ])->assertStatus(403);
    }

    public function test_kasir_can_view_transactions(): void
    {
        Sanctum::actingAs(User::factory()->kasir()->create());

        $this->getJson('/api/v1/transactions')->assertOk();
    }
}
