<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL for MySQL enum modifications to avoid data truncation issues
        // This approach properly adds new values to existing enums
        
        // Add 'voided' to status enum
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'processing', 'completed', 'cancelled', 'voided') DEFAULT 'pending'");
        
        // Add 'voided' to payment_status enum
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_status` ENUM('unpaid', 'partial', 'paid', 'voided') DEFAULT 'unpaid'");
        
        // Modify kds_status - it's a string column, so change any 'voided' values to 'cancelled' 
        // since KDS doesn't need a 'voided' status (it's internal accounting)
        // Don't change the column type - keep it as string to avoid data issues
    }

    public function down(): void
    {
        // Update any 'voided' statuses back to 'cancelled' before removing the enum value
        DB::statement("UPDATE `orders` SET `status` = 'cancelled' WHERE `status` = 'voided'");
        DB::statement("UPDATE `orders` SET `payment_status` = 'unpaid' WHERE `payment_status` = 'voided'");
        
        // Revert enums
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending'");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_status` ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid'");
    }
};
