<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedInteger('per_customer_limit')->nullable()->after('usage_limit');
            $table->boolean('first_time_only')->default(false)->after('per_customer_limit');
            $table->boolean('can_combine_with_manual_discount')->default(false)->after('first_time_only');
            $table->boolean('can_combine_with_points')->default(false)->after('can_combine_with_manual_discount');
            $table->foreignId('free_product_id')->nullable()->after('can_combine_with_points')->constrained('products')->nullOnDelete();
            $table->unsignedInteger('free_quantity')->default(1)->after('free_product_id');
            $table->decimal('issue_on_min_spend', 10, 2)->nullable()->after('free_quantity');
            $table->unsignedSmallInteger('issue_expires_in_days')->nullable()->after('issue_on_min_spend');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('free_product_id');
            $table->dropColumn([
                'per_customer_limit',
                'first_time_only',
                'can_combine_with_manual_discount',
                'can_combine_with_points',
                'free_quantity',
                'issue_on_min_spend',
                'issue_expires_in_days',
            ]);
        });
    }
};

