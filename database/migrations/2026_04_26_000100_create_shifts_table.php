<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Shift timing
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            
            // Cash tracking
            $table->decimal('opening_cash', 10, 2)->default(0);
            $table->decimal('expected_cash', 10, 2)->default(0);
            $table->decimal('actual_cash', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            
            // Sales summary (calculated on close)
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->decimal('cash_sales', 10, 2)->default(0);
            $table->decimal('card_sales', 10, 2)->default(0);
            $table->decimal('ewallet_sales', 10, 2)->default(0);
            $table->decimal('qris_sales', 10, 2)->default(0);
            $table->integer('order_count')->default(0);
            
            // Refunds/voids
            $table->decimal('refunds_total', 10, 2)->default(0);
            $table->integer('refunds_count')->default(0);
            
            // Status
            $table->enum('status', ['open', 'closed'])->default('open');
            
            // Notes
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            
            $table->timestamps();
            
            // Index for quick lookup of open shift
            $table->index(['tenant_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Movement type: cash_in, cash_out, adjustment
            $table->enum('type', ['cash_in', 'cash_out', 'adjustment']);
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['shift_id', 'type']);
        });

        // Add shift_id to orders table to track which shift an order belongs to
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('tenant_id')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });
        
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('shifts');
    }
};
