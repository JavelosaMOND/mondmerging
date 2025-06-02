<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('report_types', function (Blueprint $table) {
            $table->json('allowed_file_types')->nullable()->after('deadline');
        });
    }

    public function down()
    {
        Schema::table('report_types', function (Blueprint $table) {
            $table->dropColumn('allowed_file_types');
        });
    }
};
