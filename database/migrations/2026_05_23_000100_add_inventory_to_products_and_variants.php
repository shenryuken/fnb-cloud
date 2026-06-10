<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Whether stock is tracked for this product. When false the product
            // is treated as always available (e.g. services, made-to-order).
            $table->boolean('track_stock')->default(false)->after('is_available');
            // Current on-hand quantity (used when the product has no variants).
            $table->integer('stock_quantity')->default(0)->after('track_stock');
            // Threshold at or below which the product is flagged "low stock".
            $table->integer('low_stock_threshold')->default(0)->after('stock_quantity');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('track_stock')->default(false)->after('is_active');
            $table->integer('stock_quantity')->default(0)->after('track_stock');
            $table->integer('low_stock_threshold')->default(0)->after('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['track_stock', 'stock_quantity', 'low_stock_threshold']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['track_stock', 'stock_quantity', 'low_stock_threshold']);
        });
    }
};
