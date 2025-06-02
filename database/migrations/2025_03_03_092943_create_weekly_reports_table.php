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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('report_type_id')->constrained()->onDelete('cascade');
            $table->string('month');
            $table->integer('week_number');
            $table->integer('num_of_clean_up_sites');
            $table->integer('num_of_participants');
            $table->integer('num_of_barangays');
            $table->decimal('total_volume');
            $table->date('deadline')->nullable();
            $table->string('status')->default('pending');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
