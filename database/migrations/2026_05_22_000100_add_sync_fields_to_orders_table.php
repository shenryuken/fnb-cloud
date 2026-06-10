<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds offline-sync support to orders:
 *  - client_uuid: client-generated idempotency key for create-only sync.
 *    Unique so a replayed/duplicated sync request resolves to the same order.
 *  - source: where the order originated ('web', 'api', 'offline'), for reporting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->unique()->after('tenant_id');
            $table->string('source', 20)->default('web')->after('client_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->dropColumn(['client_uuid', 'source']);
        });
    }
};
