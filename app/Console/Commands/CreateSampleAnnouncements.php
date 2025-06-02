<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class CreateSampleAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:sample {--force : Force clear existing announcements}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample announcements for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating sample announcements...');

        // Clear existing announcements if required
        if ($this->option('force')) {
            Announcement::truncate();
            $this->info('All existing announcements cleared.');
        }

        // Award announcement
        Announcement::create([
            'title' => 'Congratulations to Barangay Malabon for Outstanding Performance',
            'content' => '<p>The Department of the Interior and Local Government (DILG) is proud to announce that <strong>Barangay Malabon</strong> has been awarded the "Outstanding Barangay" recognition for their exemplary performance in community service and timely report submissions.</p><p>They achieved a 100% compliance rate for the fiscal year 2023-2024.</p>',
            'image_path' => null,
            'button_text' => 'View Details',
            'button_link' => '#',
            'is_active' => true,
            'background_color' => '#003366',
            'priority' => 10,
        ]);

        // Update announcement
        Announcement::create([
            'title' => 'Important Update: New Quarterly Reporting System',
            'content' => '<p>Starting July 1, 2025, we will be implementing a new quarterly reporting system with enhanced features and improved analytical tools.</p><p>All barangay administrators are required to attend the online training session on June 15, 2025.</p>',
            'image_path' => null,
            'button_text' => 'Learn More',
            'button_link' => '#',
            'is_active' => true,
            'background_color' => '#1e40af',
            'priority' => 9,
        ]);

        // Event announcement
        Announcement::create([
            'title' => 'Upcoming Conference: Barangay Development Summit 2025',
            'content' => '<p>The annual Barangay Development Summit will be held on August 10-12, 2025 at the Manila Convention Center.</p><p>Join us for three days of workshops, presentations, and networking opportunities focused on improving local governance.</p>',
            'image_path' => null,
            'button_text' => 'Register Now',
            'button_link' => '#',
            'is_active' => true,
            'background_color' => '#0369a1',
            'priority' => 8,
        ]);

        $this->info('Sample announcements created successfully!');
        $this->info('Note: Add images through the admin interface for a complete experience.');

        return Command::SUCCESS;
    }
} 