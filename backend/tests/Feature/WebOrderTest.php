<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WebOrderTest extends TestCase
{
    use RefreshDatabase;

    private function placeOrder(Product $product, int $qty = 1)
    {
        return $this->postJson('/api/v1/public/orders', [
            'customer_name' => 'Budi',
            'items' => [['product_id' => $product->id, 'quantity' => $qty]],
            'payment_method' => 'cash',
        ]);
    }

    public function test_public_menu_returns_only_active_products(): void
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $this->getJson('/api/v1/public/menu')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_public_order_creates_pending_transaction(): void
    {
        $p = Product::factory()->create(['stock' => 10]);

        $this->placeOrder($p, 2)
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonStructure(['data' => ['order_code', 'items']]);

        $this->assertSame(8, $p->fresh()->stock);
    }

    public function test_order_tracking_by_code(): void
    {
        $p = Product::factory()->create();
        $code = $this->placeOrder($p)->json('data.order_code');

        $this->getJson("/api/v1/public/orders/{$code}")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');

        $this->getJson('/api/v1/public/orders/WS-TIDAK')->assertStatus(404);
    }

    public function test_order_with_insufficient_stock_is_rejected(): void
    {
        $p = Product::factory()->create(['stock' => 1]);

        $this->placeOrder($p, 2)->assertStatus(409);

        $this->assertSame(1, $p->fresh()->stock);
        $this->assertSame(0, Transaction::count());
    }

    public function test_kasir_accepts_then_completes_order_with_change(): void
    {
        $p = Product::factory()->create(['price' => 8000, 'stock' => 10]);
        $code = $this->placeOrder($p, 2)->json('data.order_code');
        $id = Transaction::where('order_code', $code)->value('id');

        $kasir = User::factory()->kasir()->create();
        Sanctum::actingAs($kasir);

        $this->getJson('/api/v1/orders')->assertOk()->assertJsonCount(1, 'data');

        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'accepted'])
            ->assertOk()->assertJsonPath('data.status', 'accepted');

        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'completed', 'pay_amount' => 20000])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.change_amount', 4000)
            ->assertJsonPath('data.user_id', $kasir->id);

        $this->assertSame(8, $p->fresh()->stock);
    }

    public function test_completing_with_short_payment_is_rejected(): void
    {
        $p = Product::factory()->create(['price' => 8000]);
        $code = $this->placeOrder($p, 2)->json('data.order_code');
        $id = Transaction::where('order_code', $code)->value('id');

        Sanctum::actingAs(User::factory()->kasir()->create());
        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'accepted'])->assertOk();

        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'completed', 'pay_amount' => 10000])
            ->assertStatus(422);
        $this->assertSame('accepted', Transaction::find($id)->status);
    }

    public function test_cancelling_restores_stock(): void
    {
        $p = Product::factory()->create(['stock' => 10]);
        $code = $this->placeOrder($p, 3)->json('data.order_code');
        $id = Transaction::where('order_code', $code)->value('id');
        $this->assertSame(7, $p->fresh()->stock);

        Sanctum::actingAs(User::factory()->kasir()->create());
        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'cancelled'])->assertOk();

        $this->assertSame(10, $p->fresh()->stock);
    }

    public function test_invalid_transitions_return_409_and_do_not_double_restore_stock(): void
    {
        $p = Product::factory()->create(['stock' => 10]);
        $code = $this->placeOrder($p, 3)->json('data.order_code');
        $id = Transaction::where('order_code', $code)->value('id');

        Sanctum::actingAs(User::factory()->kasir()->create());

        // pending tidak boleh langsung completed
        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'completed', 'pay_amount' => 99999])
            ->assertStatus(409);

        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'cancelled'])->assertOk();
        // sudah cancelled tidak boleh dibatalkan lagi
        $this->patchJson("/api/v1/orders/{$id}/status", ['status' => 'cancelled'])->assertStatus(409);

        $this->assertSame(10, $p->fresh()->stock);
    }

    public function test_pending_orders_are_not_counted_in_daily_report(): void
    {
        $p = Product::factory()->create(['price' => 8000]);
        $this->placeOrder($p, 1);

        Sanctum::actingAs(User::factory()->owner()->create());
        $this->getJson('/api/v1/reports/daily')
            ->assertOk()
            ->assertJsonPath('data.total_omset', 0)
            ->assertJsonPath('data.total_transactions', 0);
    }

    public function test_order_validation(): void
    {
        $this->postJson('/api/v1/public/orders', [])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['customer_name', 'items', 'payment_method']]);
    }
}
