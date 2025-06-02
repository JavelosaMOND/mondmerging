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
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->integer('sem_number')->change();
            $table->string('status')->default('no submission')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semestral_reports', function (Blueprint $table) {
            $table->string('sem_number')->change();
            $table->string('status')->default('pending')->change();
        });
    }
};
