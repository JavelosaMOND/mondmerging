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
        // Drop and re-add status as string for all report tables
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->string('status', 20)->default('no submission');
        });

        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->string('status', 20)->default('no submission');
        });

        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->string('status', 20)->default('no submission');
        });

        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->string('status', 20)->default('no submission');
        });

        Schema::table('annual_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('annual_reports', function (Blueprint $table) {
            $table->string('status', 20)->default('no submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to enum (not recommended, but for rollback)
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        Schema::table('annual_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('annual_reports', function (Blueprint $table) {
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });
    }
};
