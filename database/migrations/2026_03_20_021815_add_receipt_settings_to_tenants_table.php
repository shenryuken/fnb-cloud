<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('phone');
            $table->string('receipt_email')->nullable()->after('logo_url');
            $table->text('receipt_header')->nullable()->after('receipt_email');
            $table->text('receipt_footer')->nullable()->after('receipt_header');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'receipt_email', 'receipt_header', 'receipt_footer']);
        });
    }
};
