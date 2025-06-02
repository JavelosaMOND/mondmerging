<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Announcement;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('category')->default('announcement')->after('button_link');
        });

        // Update existing announcements with categories based on their titles
        if (Schema::hasTable('announcements')) {
            $announcements = Announcement::all();
            foreach ($announcements as $announcement) {
                $title = strtolower($announcement->title);
                $category = 'announcement';

                if (Str::contains($title, ['congratulations', 'award', 'recognition', 'achievement'])) {
                    $category = 'recognition';
                } elseif (Str::contains($title, ['update', 'notice', 'alert'])) {
                    $category = 'important_update';
                } elseif (Str::contains($title, ['event', 'meeting', 'conference'])) {
                    $category = 'upcoming_event';
                }

                $announcement->update(['category' => $category]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}; 