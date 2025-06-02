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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('report_type_id')->constrained('report_types')->onDelete('cascade');
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'semestral', 'annual']);
            $table->json('period_data')->nullable(); // Stores period-specific data like week_number, month, quarter, etc.
            $table->dateTime('deadline')->nullable();
            $table->string('status')->default('no submission');
            $table->text('remarks')->nullable();
            
            // Fields for cleanup reports (can be null for other report types)
            $table->integer('num_of_clean_up_sites')->nullable();
            $table->integer('num_of_participants')->nullable();
            $table->integer('num_of_barangays')->nullable();
            $table->decimal('total_volume', 10, 2)->nullable();
            
            // Legacy fields (to be eventually removed)
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            
            $table->timestamps();
            
            // Add indexes for commonly queried columns
            $table->index('user_id');
            $table->index('report_type_id');
            $table->index('frequency');
            $table->index('status');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
}; 