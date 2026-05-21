<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE `users` SET `pin` = LEFT(`pin`, 6) WHERE `pin` IS NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement('UPDATE "users" SET "pin" = SUBSTRING("pin" FROM 1 FOR 6) WHERE "pin" IS NOT NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement('UPDATE "users" SET "pin" = SUBSTR("pin", 1, 6) WHERE "pin" IS NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 6)->nullable()->change();
        });
    }
};
