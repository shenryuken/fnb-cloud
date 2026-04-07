<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->time('business_day_start_time')->default('00:00')->after('tax_rate');
            $table->time('business_day_end_time')->default('23:59')->after('business_day_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['business_day_start_time', 'business_day_end_time']);
        });
    }
};

