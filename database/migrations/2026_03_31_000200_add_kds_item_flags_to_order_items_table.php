<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('kds_is_ready')->default(false)->after('notes');
            $table->timestamp('kds_ready_at')->nullable()->after('kds_is_ready');
            $table->boolean('kds_is_served')->default(false)->after('kds_ready_at');
            $table->timestamp('kds_served_at')->nullable()->after('kds_is_served');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['kds_is_ready', 'kds_ready_at', 'kds_is_served', 'kds_served_at']);
        });
    }
};

