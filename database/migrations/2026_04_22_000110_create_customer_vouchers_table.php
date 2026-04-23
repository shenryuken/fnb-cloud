<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('code');
            $table->foreignId('issued_from_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('used_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'customer_id', 'voucher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_vouchers');
    }
};

