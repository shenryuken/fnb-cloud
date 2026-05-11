<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('status')->default('active')->after('is_active');
            $table->string('plan')->default('standard')->after('status');
            $table->dateTime('trial_ends_at')->nullable()->after('plan');
            $table->dateTime('suspended_at')->nullable()->after('trial_ends_at');
            $table->string('suspended_reason')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['status', 'plan', 'trial_ends_at', 'suspended_at', 'suspended_reason']);
        });
    }
};

