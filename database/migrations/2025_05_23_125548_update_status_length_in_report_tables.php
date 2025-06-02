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
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
        Schema::table('annual_reports', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->string('status', 10)->change();
        });
        Schema::table('monthly_reports', function (Blueprint $table) {
            $table->string('status', 10)->change();
        });
        Schema::table('quarterly_reports', function (Blueprint $table) {
            $table->string('status', 10)->change();
        });
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->string('status', 10)->change();
        });
        Schema::table('annual_reports', function (Blueprint $table) {
            $table->string('status', 10)->change();
        });
    }
};
