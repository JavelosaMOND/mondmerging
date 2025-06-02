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
        // Modify weekly_reports table
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        // Modify monthly_reports table
        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        // Modify quarterly_reports table
        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        // Modify semestral_reports table
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });

        // Modify annual_reports table
        Schema::table('annual_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['submitted', 'no submission'])->default('no submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert weekly_reports table
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });

        // Revert monthly_reports table
        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });

        // Revert quarterly_reports table
        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });

        // Revert semestral_reports table
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });

        // Revert annual_reports table
        Schema::table('annual_reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });
    }
};
