<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Modify status enum to add 'voided'
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'voided'])
                ->default('pending')
                ->change();
            
            // Modify payment_status enum to add 'voided'
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'voided'])
                ->default('unpaid')
                ->change();
            
            // Modify kds_status enum to add 'cancelled' if needed
            if (Schema::hasColumn('orders', 'kds_status')) {
                $table->enum('kds_status', ['pending', 'preparing', 'ready', 'cancelled'])
                    ->nullable()
                    ->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
            
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])
                ->default('unpaid')
                ->change();
        });
    }
};
