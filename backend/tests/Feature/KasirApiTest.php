<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KasirApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_berhasil_mengembalikan_token_dan_role(): void
    {
        User::factory()->create([
            'email' => 'kasir@warkop.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KASIR,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'kasir@warkop.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.role', 'kasir')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);
    }

    public function test_login_gagal_kredensial_salah_401(): void
    {
        User::factory()->create([
            'email' => 'kasir@warkop.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KASIR,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'kasir@warkop.test',
            'password' => 'salah-password',
        ])->assertStatus(401);
    }

    public function test_akses_endpoint_terproteksi_tanpa_token_401(): void
    {
        $this->getJson('/api/v1/products')->assertStatus(401);
    }

    public function test_admin_dapat_menambah_produk_201(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/products', [
            'name' => 'Matcha Latte',
            'price' => 18000,
            'stock' => 20,
            'min_stock' => 5,
        ])->assertCreated()
          ->assertJsonPath('data.name', 'Matcha Latte');
    }

    public function test_kasir_ditolak_menambah_produk_403(): void
    {
        $kasir = User::factory()->create(['role' => User::ROLE_KASIR]);
        Sanctum::actingAs($kasir);

        $this->postJson('/api/v1/products', [
            'name' => 'Matcha Latte',
            'price' => 18000,
            'stock' => 20,
        ])->assertStatus(403);
    }

    public function test_validasi_produk_gagal_422(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/products', [
            'name' => '',
            'price' => -5000,
        ])->assertStatus(422)
          ->assertJsonStructure(['message', 'errors']);
    }

    public function test_checkout_transaksi_pos_berhasil_dan_stok_berkurang(): void
    {
        $kasir = User::factory()->create(['role' => User::ROLE_KASIR]);
        $product = Product::create([
            'name' => 'Kopi Aren',
            'price' => 15000,
            'stock' => 10,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        Sanctum::actingAs($kasir);

        $response = $this->postJson('/api/v1/transactions', [
            'payment_method' => 'cash',
            'pay_amount' => 50000,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total_price', 30000)
            ->assertJsonPath('data.change_amount', 20000)
            ->assertJsonPath('data.details.0.product.name', 'Kopi Aren');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);
    }

    public function test_checkout_stok_tidak_cukup_409(): void
    {
        $kasir = User::factory()->create(['role' => User::ROLE_KASIR]);
        $product = Product::create([
            'name' => 'Roti Bakar',
            'price' => 20000,
            'stock' => 1,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        Sanctum::actingAs($kasir);

        $this->postJson('/api/v1/transactions', [
            'payment_method' => 'qris',
            'pay_amount' => 40000,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertStatus(409);
    }

    public function test_kasir_tidak_dapat_melihat_transaksi_milik_kasir_lain_403(): void
    {
        $kasirA = User::factory()->create(['role' => User::ROLE_KASIR]);
        $kasirB = User::factory()->create(['role' => User::ROLE_KASIR]);

        $trx = Transaction::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $kasirA->id,
            'total_price' => 15000,
            'pay_amount' => 20000,
            'change_amount' => 5000,
            'payment_method' => 'cash',
            'source' => Transaction::SOURCE_POS,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        Sanctum::actingAs($kasirB);

        $this->getJson("/api/v1/transactions/{$trx->id}")->assertStatus(403);
    }

    public function test_laporan_harian_hanya_untuk_admin(): void
    {
        $kasir = User::factory()->create(['role' => User::ROLE_KASIR]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Sanctum::actingAs($kasir);
        $this->getJson('/api/v1/reports/daily')->assertStatus(403);

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/reports/daily')
            ->assertOk()
            ->assertJsonStructure(['date', 'total_omset', 'total_transactions', 'transactions']);
    }
}