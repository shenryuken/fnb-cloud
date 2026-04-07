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
        // 1. Create addon_groups table
        Schema::create('addon_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('min_select')->default(0); // 0 means optional
            $table->integer('max_select')->default(1); // 1 means radio, >1 means checkbox
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Update product_addons to belong to a group
        Schema::table('product_addons', function (Blueprint $table) {
            $table->foreignId('addon_group_id')->nullable()->after('tenant_id')->constrained('addon_groups')->onDelete('cascade');
        });

        // 3. Update addon_product pivot to link products to groups instead of individual addons
        // This allows assigning a whole group (e.g. "Choose your Sauce") to a product.
        Schema::create('addon_group_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('addon_group_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_group_product');

        Schema::table('product_addons', function (Blueprint $table) {
            $table->dropForeign(['addon_group_id']);
            $table->dropColumn('addon_group_id');
        });

        Schema::dropIfExists('addon_groups');
    }
};
