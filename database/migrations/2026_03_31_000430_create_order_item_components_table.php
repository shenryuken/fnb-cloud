<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('group_name', 80)->nullable();
            $table->string('name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_components');
    }
};

