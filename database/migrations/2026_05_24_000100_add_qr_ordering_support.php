<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QR self-ordering support.
 *
 *  - restaurant_tables.qr_token: a unique, unguessable token encoded into the
 *    table's QR code. Used to resolve the table (and its tenant) on the public,
 *    unauthenticated ordering page.
 *  - tenants.qr_ordering_enabled: master switch so a venue can turn the public
 *    QR ordering flow on/off without deleting tokens.
 *  - tenants.qr_ordering_requires_name: optionally require a guest name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('code');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('qr_ordering_enabled')->default(false)->after('is_busy');
            $table->boolean('qr_ordering_requires_name')->default(true)->after('qr_ordering_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['qr_ordering_enabled', 'qr_ordering_requires_name']);
        });
    }
};
