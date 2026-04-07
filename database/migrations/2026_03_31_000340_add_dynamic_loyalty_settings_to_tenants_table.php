<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('points_earn_points', 10, 4)->default(1)->after('points_min_redeem');
            $table->decimal('points_earn_amount', 10, 2)->default(1)->after('points_earn_points');
            $table->unsignedInteger('points_redeem_points')->default(100)->after('points_earn_amount');
            $table->decimal('points_redeem_amount', 10, 2)->default(1)->after('points_redeem_points');
            $table->boolean('points_promo_is_enabled')->default(false)->after('points_redeem_amount');
            $table->decimal('points_promo_multiplier', 10, 2)->default(1)->after('points_promo_is_enabled');
            $table->timestamp('points_promo_starts_at')->nullable()->after('points_promo_multiplier');
            $table->timestamp('points_promo_ends_at')->nullable()->after('points_promo_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'points_earn_points',
                'points_earn_amount',
                'points_redeem_points',
                'points_redeem_amount',
                'points_promo_is_enabled',
                'points_promo_multiplier',
                'points_promo_starts_at',
                'points_promo_ends_at',
            ]);
        });
    }
};

