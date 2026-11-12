<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->integer('total_price');
            $table->integer('pay_amount')->default(0);
            $table->integer('change_amount')->default(0);
            $table->enum('payment_method', ['cash', 'qris']);
            $table->enum('source', ['pos', 'web'])->default('pos');
            $table->enum('status', ['pending', 'accepted', 'completed', 'cancelled'])->default('completed');
            $table->string('order_code')->nullable()->unique();
            $table->string('customer_name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
