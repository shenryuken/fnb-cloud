<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('points_earn_rate', 10, 4)->default(1)->after('tax_rate');
            $table->decimal('points_redeem_value_per_100', 10, 2)->default(1)->after('points_earn_rate');
            $table->unsignedInteger('points_min_redeem')->default(0)->after('points_redeem_value_per_100');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['points_earn_rate', 'points_redeem_value_per_100', 'points_min_redeem']);
        });
    }
};

