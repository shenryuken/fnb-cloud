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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Table 1", "T1", "Patio 3"
            $table->string('code')->nullable(); // Short code for quick lookup
            $table->integer('capacity')->default(4); // Number of seats
            $table->enum('status', ['available', 'occupied', 'reserved', 'dirty'])->default('available');
            $table->enum('shape', ['square', 'rectangle', 'circle', 'oval'])->default('square');
            
            // Position on floor plan (grid-based)
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->integer('width')->default(1); // Grid units
            $table->integer('height')->default(1); // Grid units
            
            // Floor/area organization
            $table->string('floor')->nullable(); // e.g., "Main Floor", "Patio", "VIP"
            
            // Merged tables tracking
            $table->foreignId('merged_into_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->json('merged_table_ids')->nullable(); // IDs of tables merged into this one
            
            // Turn time tracking
            $table->timestamp('occupied_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->string('reservation_name')->nullable();
            $table->string('reservation_phone')->nullable();
            $table->text('reservation_notes')->nullable();
            
            // Current order reference
            $table->foreignId('current_order_id')->nullable();
            
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'floor']);
        });

        // Add table_id to orders for proper relationship
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('table_id')->nullable()->after('table_number')->constrained('restaurant_tables')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropColumn('table_id');
        });
        
        Schema::dropIfExists('restaurant_tables');
    }
};
