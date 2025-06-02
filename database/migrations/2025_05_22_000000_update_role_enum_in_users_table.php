<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First, modify the column to be a string temporarily
            $table->string('role')->change();
        });

        // Then update the column to be an enum with the new values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cluster', 'barangay', 'Facilitator')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to the original enum values
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cluster', 'barangay')");
        });
    }
}; 