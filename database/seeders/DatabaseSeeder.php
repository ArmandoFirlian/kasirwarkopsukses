<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Akun Autentikasi Kasir & Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@warkop.test'],
            [
                'name' => 'Owner Warkop',
                'password' => bcrypt('password'),
                'role' => 'owner',
            ]
        );

        $kasir = User::firstOrCreate(
            ['email' => 'kasir@warkop.test'],
            [
                'name' => 'Kasir Warkop',
                'password' => bcrypt('password'),
                'role' => 'kasir',
            ]
        );

        // 2. Data Menu Asli dari Supabase (44 Produk)
        $products = [
            ['id' => 1, 'name' => 'Signature Keycu', 'price' => 23000, 'stock' => 19, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:53:39', 'updated_at' => '2026-10-14 16:00:02'],
            ['id' => 2, 'name' => 'Amelcie', 'price' => 17000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:53:48', 'updated_at' => '2026-10-12 14:53:48'],
            ['id' => 3, 'name' => 'Nasi Telur Sukses', 'price' => 16000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:53:56', 'updated_at' => '2026-10-12 14:53:56'],
            ['id' => 4, 'name' => 'Red Kiss', 'price' => 23000, 'stock' => 19, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:57:31', 'updated_at' => '2026-10-12 19:00:37'],
            ['id' => 5, 'name' => 'Cinna-pine', 'price' => 23000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:57:58', 'updated_at' => '2026-10-12 14:57:58'],
            ['id' => 6, 'name' => 'Americano', 'price' => 15000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:58:11', 'updated_at' => '2026-10-14 16:05:54'],
            ['id' => 7, 'name' => 'Amestro', 'price' => 17000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:58:24', 'updated_at' => '2026-10-12 14:58:24'],
            ['id' => 8, 'name' => 'Coffee Latte', 'price' => 20000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:58:42', 'updated_at' => '2026-10-12 14:58:42'],
            ['id' => 9, 'name' => 'Moccacino', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:58:56', 'updated_at' => '2026-10-12 14:58:56'],
            ['id' => 10, 'name' => 'Gula Aren', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:59:14', 'updated_at' => '2026-10-12 14:59:14'],
            ['id' => 11, 'name' => 'Salted Caramel', 'price' => 21000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:59:31', 'updated_at' => '2026-10-12 14:59:31'],
            ['id' => 12, 'name' => 'Roma', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 14:59:51', 'updated_at' => '2026-10-12 14:59:51'],
            ['id' => 13, 'name' => 'Butterscotch', 'price' => 21000, 'stock' => 19, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:00:05', 'updated_at' => '2026-10-14 15:44:43'],
            ['id' => 14, 'name' => 'Vanilla', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:00:20', 'updated_at' => '2026-10-12 15:00:20'],
            ['id' => 15, 'name' => 'HazelNut', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:00:39', 'updated_at' => '2026-10-12 15:00:39'],
            ['id' => 16, 'name' => 'Banana', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:00:52', 'updated_at' => '2026-10-12 15:00:52'],
            ['id' => 17, 'name' => 'Coklat Clasic', 'price' => 14000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:01:25', 'updated_at' => '2026-10-12 15:01:25'],
            ['id' => 18, 'name' => 'Coklat Susu', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:01:42', 'updated_at' => '2026-10-12 15:01:42'],
            ['id' => 19, 'name' => 'Coklat Matcha', 'price' => 21000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:01:56', 'updated_at' => '2026-10-12 15:01:56'],
            ['id' => 20, 'name' => 'Matcha', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:02:08', 'updated_at' => '2026-10-12 15:02:08'],
            ['id' => 21, 'name' => 'Matcha Cloud', 'price' => 20000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:02:23', 'updated_at' => '2026-10-12 15:02:23'],
            ['id' => 22, 'name' => 'Red Velvet', 'price' => 19000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:02:40', 'updated_at' => '2026-10-12 15:02:40'],
            ['id' => 23, 'name' => 'Wedang Tape', 'price' => 15000, 'stock' => 19, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:03:06', 'updated_at' => '2026-10-12 19:00:36'],
            ['id' => 24, 'name' => 'Wedang Jahe Roti', 'price' => 15000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:09:01', 'updated_at' => '2026-10-12 15:09:01'],
            ['id' => 25, 'name' => 'Wedang Tape Panas', 'price' => 13000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:09:11', 'updated_at' => '2026-10-12 15:09:11'],
            ['id' => 26, 'name' => 'Wedang Jahe Roti Panas', 'price' => 13000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:09:21', 'updated_at' => '2026-10-12 15:09:21'],
            ['id' => 27, 'name' => 'Wedang Jahe', 'price' => 13000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:09:36', 'updated_at' => '2026-10-12 15:09:36'],
            ['id' => 28, 'name' => 'Wedang Jahe Susu', 'price' => 13000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:09:47', 'updated_at' => '2026-10-12 15:09:47'],
            ['id' => 29, 'name' => 'Teh', 'price' => 10000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:10:01', 'updated_at' => '2026-10-12 15:10:01'],
            ['id' => 30, 'name' => 'Mie Gongso', 'price' => 16000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:10:14', 'updated_at' => '2026-10-12 16:45:37'],
            ['id' => 31, 'name' => 'Mie Kuah Gongso', 'price' => 15000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:10:55', 'updated_at' => '2026-10-12 15:10:55'],
            ['id' => 33, 'name' => 'Nasi Goreng Keycu', 'price' => 20000, 'stock' => 19, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:11:18', 'updated_at' => '2026-10-14 15:44:43'],
            ['id' => 34, 'name' => 'Nasi Sambal Matah', 'price' => 18000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:11:31', 'updated_at' => '2026-10-12 15:11:31'],
            ['id' => 35, 'name' => 'Omelet Mie', 'price' => 12000, 'stock' => 19, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:11:45', 'updated_at' => '2026-10-14 16:00:03'],
            ['id' => 36, 'name' => 'Extra Telur', 'price' => 5000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:12:10', 'updated_at' => '2026-10-12 15:12:10'],
            ['id' => 37, 'name' => 'Pangsit Ayam', 'price' => 15000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:12:19', 'updated_at' => '2026-10-12 15:12:19'],
            ['id' => 38, 'name' => 'Roti Goreng', 'price' => 15000, 'stock' => 19, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:12:28', 'updated_at' => '2026-10-14 16:00:04'],
            ['id' => 39, 'name' => 'Pisang Bakar Warsus', 'price' => 10000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:12:42', 'updated_at' => '2026-10-12 15:12:42'],
            ['id' => 40, 'name' => 'Mount Blanc', 'price' => 25000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:13:33', 'updated_at' => '2026-10-14 16:07:36'],
            ['id' => 41, 'name' => 'French Fries', 'price' => 12000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:13:43', 'updated_at' => '2026-10-12 15:13:43'],
            ['id' => 42, 'name' => 'Cheesecake', 'price' => 15000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:13:53', 'updated_at' => '2026-10-12 15:13:53'],
            ['id' => 43, 'name' => 'Dimsum', 'price' => 20000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 15:13:58', 'updated_at' => '2026-10-12 15:13:58'],
            ['id' => 44, 'name' => 'Wedang Badeg', 'price' => 16000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-12 16:40:30', 'updated_at' => '2026-10-12 16:40:30'],
            ['id' => 45, 'name' => 'Ciggaretess Signature', 'price' => 25000, 'stock' => 20, 'min_stock' => 5, 'is_active' => true, 'created_at' => '2026-10-14 16:17:06', 'updated_at' => '2026-10-14 16:17:06'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['id' => $product['id']], $product);
        }

        // 3. Data Transaksi Asli dari Supabase (4 Transaksi POS)
        $transactions = [
            [
                'id' => 1,
                'invoice_number' => 'INV-1791831637-349',
                'total_price' => 38000,
                'pay_amount' => 50000,
                'change_amount' => 12000,
                'payment_method' => 'cash',
                'source' => Transaction::SOURCE_POS,
                'status' => Transaction::STATUS_COMPLETED,
                'user_id' => $kasir->id,
                'created_at' => '2026-10-12 19:00:37',
                'updated_at' => '2026-10-12 19:00:37',
            ],
            [
                'id' => 2,
                'invoice_number' => 'INV-1791831683-335',
                'total_price' => 15000,
                'pay_amount' => 20000,
                'change_amount' => 5000,
                'payment_method' => 'cash',
                'source' => Transaction::SOURCE_POS,
                'status' => Transaction::STATUS_COMPLETED,
                'user_id' => $kasir->id,
                'created_at' => '2026-10-12 19:01:23',
                'updated_at' => '2026-10-12 19:01:23',
            ],
            [
                'id' => 3,
                'invoice_number' => 'INV-1791992684-286',
                'total_price' => 41000,
                'pay_amount' => 41000,
                'change_amount' => 0,
                'payment_method' => 'qris',
                'source' => Transaction::SOURCE_POS,
                'status' => Transaction::STATUS_COMPLETED,
                'user_id' => $kasir->id,
                'created_at' => '2026-10-14 15:44:44',
                'updated_at' => '2026-10-14 15:44:44',
            ],
            [
                'id' => 4,
                'invoice_number' => 'INV-1791993604-841',
                'total_price' => 50000,
                'pay_amount' => 100000,
                'change_amount' => 50000,
                'payment_method' => 'cash',
                'source' => Transaction::SOURCE_POS,
                'status' => Transaction::STATUS_COMPLETED,
                'user_id' => $kasir->id,
                'created_at' => '2026-10-14 16:00:04',
                'updated_at' => '2026-10-14 16:00:04',
            ],
        ];

        foreach ($transactions as $tx) {
            Transaction::updateOrCreate(['id' => $tx['id']], $tx);
        }

        // 4. Detail Transaksi Asli (8 Item)
        $details = [
            ['id' => 1, 'transaction_id' => 1, 'product_id' => 23, 'quantity' => 1, 'price' => 15000, 'subtotal' => 15000, 'created_at' => '2026-10-12 19:00:38', 'updated_at' => '2026-10-12 19:00:38'],
            ['id' => 2, 'transaction_id' => 1, 'product_id' => 4, 'quantity' => 1, 'price' => 23000, 'subtotal' => 23000, 'created_at' => '2026-10-12 19:00:38', 'updated_at' => '2026-10-12 19:00:38'],
            ['id' => 3, 'transaction_id' => 2, 'product_id' => 6, 'quantity' => 1, 'price' => 15000, 'subtotal' => 15000, 'created_at' => '2026-10-12 19:01:23', 'updated_at' => '2026-10-12 19:01:23'],
            ['id' => 4, 'transaction_id' => 3, 'product_id' => 13, 'quantity' => 1, 'price' => 21000, 'subtotal' => 21000, 'created_at' => '2026-10-14 15:44:44', 'updated_at' => '2026-10-14 15:44:44'],
            ['id' => 5, 'transaction_id' => 3, 'product_id' => 33, 'quantity' => 1, 'price' => 20000, 'subtotal' => 20000, 'created_at' => '2026-10-14 15:44:45', 'updated_at' => '2026-10-14 15:44:45'],
            ['id' => 6, 'transaction_id' => 4, 'product_id' => 1, 'quantity' => 1, 'price' => 23000, 'subtotal' => 23000, 'created_at' => '2026-10-14 16:00:05', 'updated_at' => '2026-10-14 16:00:05'],
            ['id' => 7, 'transaction_id' => 4, 'product_id' => 35, 'quantity' => 1, 'price' => 12000, 'subtotal' => 12000, 'created_at' => '2026-10-14 16:00:05', 'updated_at' => '2026-10-14 16:00:05'],
            ['id' => 8, 'transaction_id' => 4, 'product_id' => 38, 'quantity' => 1, 'price' => 15000, 'subtotal' => 15000, 'created_at' => '2026-10-14 16:00:05', 'updated_at' => '2026-10-14 16:00:05'],
        ];

        foreach ($details as $d) {
            TransactionDetail::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
