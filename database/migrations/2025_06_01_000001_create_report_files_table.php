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
        Schema::create('report_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Polymorphic relationship fields
            $table->unsignedBigInteger('reportable_id');
            $table->string('reportable_type');
            
            // File details
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('status')->default('active');
            
            $table->timestamps();
            
            // Add indexes
            $table->index('user_id');
            $table->index(['reportable_id', 'reportable_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_files');
    }
}; 