<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Migrate weekly reports
            if (Schema::hasTable('weekly_reports')) {
                $weeklyReports = DB::table('weekly_reports')->get();
                
                foreach ($weeklyReports as $report) {
                    // Get the report_type frequency
                    $reportTypeFrequency = DB::table('report_types')
                        ->where('id', $report->report_type_id)
                        ->value('frequency') ?? 'weekly';
                    
                    // Create period data
                    $periodData = json_encode([
                        'week_number' => $report->week_number,
                        'month' => $report->month,
                    ]);
                    
                    // Insert into the new reports table
                    DB::table('reports')->insert([
                        'user_id' => $report->user_id,
                        'report_type_id' => $report->report_type_id,
                        'frequency' => $reportTypeFrequency,
                        'period_data' => $periodData,
                        'deadline' => $report->deadline,
                        'status' => $report->status,
                        'remarks' => $report->remarks,
                        'num_of_clean_up_sites' => $report->num_of_clean_up_sites,
                        'num_of_participants' => $report->num_of_participants,
                        'num_of_barangays' => $report->num_of_barangays,
                        'total_volume' => $report->total_volume,
                        'file_name' => $report->file_name,
                        'file_path' => $report->file_path,
                        'created_at' => $report->created_at,
                        'updated_at' => $report->updated_at,
                    ]);
                }
                
                Log::info('Migrated ' . count($weeklyReports) . ' weekly reports');
            }
            
            // Migrate monthly reports
            if (Schema::hasTable('monthly_reports')) {
                $monthlyReports = DB::table('monthly_reports')->get();
                
                foreach ($monthlyReports as $report) {
                    $reportTypeFrequency = DB::table('report_types')
                        ->where('id', $report->report_type_id)
                        ->value('frequency') ?? 'monthly';
                    
                    $periodData = json_encode([
                        'month' => $report->month,
                        'year' => $report->year ?? date('Y'),
                    ]);
                    
                    DB::table('reports')->insert([
                        'user_id' => $report->user_id,
                        'report_type_id' => $report->report_type_id,
                        'frequency' => $reportTypeFrequency,
                        'period_data' => $periodData,
                        'deadline' => $report->deadline,
                        'status' => $report->status,
                        'remarks' => $report->remarks,
                        'file_name' => $report->file_name,
                        'file_path' => $report->file_path,
                        'created_at' => $report->created_at,
                        'updated_at' => $report->updated_at,
                    ]);
                }
                
                Log::info('Migrated ' . count($monthlyReports) . ' monthly reports');
            }
            
            // Migrate quarterly reports
            if (Schema::hasTable('quarterly_reports')) {
                $quarterlyReports = DB::table('quarterly_reports')->get();
                
                foreach ($quarterlyReports as $report) {
                    $reportTypeFrequency = DB::table('report_types')
                        ->where('id', $report->report_type_id)
                        ->value('frequency') ?? 'quarterly';
                    
                    $periodData = json_encode([
                        'quarter' => $report->quarter,
                        'year' => $report->year ?? date('Y'),
                    ]);
                    
                    DB::table('reports')->insert([
                        'user_id' => $report->user_id,
                        'report_type_id' => $report->report_type_id,
                        'frequency' => $reportTypeFrequency,
                        'period_data' => $periodData,
                        'deadline' => $report->deadline,
                        'status' => $report->status,
                        'remarks' => $report->remarks,
                        'file_name' => $report->file_name,
                        'file_path' => $report->file_path,
                        'created_at' => $report->created_at,
                        'updated_at' => $report->updated_at,
                    ]);
                }
                
                Log::info('Migrated ' . count($quarterlyReports) . ' quarterly reports');
            }
            
            // Migrate semestral reports
            if (Schema::hasTable('semestral_reports')) {
                $semestralReports = DB::table('semestral_reports')->get();
                
                foreach ($semestralReports as $report) {
                    $reportTypeFrequency = DB::table('report_types')
                        ->where('id', $report->report_type_id)
                        ->value('frequency') ?? 'semestral';
                    
                    $periodData = json_encode([
                        'semester' => $report->semester,
                        'year' => $report->year ?? date('Y'),
                    ]);
                    
                    DB::table('reports')->insert([
                        'user_id' => $report->user_id,
                        'report_type_id' => $report->report_type_id,
                        'frequency' => $reportTypeFrequency,
                        'period_data' => $periodData,
                        'deadline' => $report->deadline,
                        'status' => $report->status,
                        'remarks' => $report->remarks,
                        'file_name' => $report->file_name,
                        'file_path' => $report->file_path,
                        'created_at' => $report->created_at,
                        'updated_at' => $report->updated_at,
                    ]);
                }
                
                Log::info('Migrated ' . count($semestralReports) . ' semestral reports');
            }
            
            // Migrate annual reports
            if (Schema::hasTable('annual_reports')) {
                $annualReports = DB::table('annual_reports')->get();
                
                foreach ($annualReports as $report) {
                    $reportTypeFrequency = DB::table('report_types')
                        ->where('id', $report->report_type_id)
                        ->value('frequency') ?? 'annual';
                    
                    $periodData = json_encode([
                        'year' => $report->year ?? date('Y'),
                    ]);
                    
                    DB::table('reports')->insert([
                        'user_id' => $report->user_id,
                        'report_type_id' => $report->report_type_id,
                        'frequency' => $reportTypeFrequency,
                        'period_data' => $periodData,
                        'deadline' => $report->deadline,
                        'status' => $report->status,
                        'remarks' => $report->remarks,
                        'file_name' => $report->file_name,
                        'file_path' => $report->file_path,
                        'created_at' => $report->created_at,
                        'updated_at' => $report->updated_at,
                    ]);
                }
                
                Log::info('Migrated ' . count($annualReports) . ' annual reports');
            }
            
            // Now migrate any report files to the new polymorphic structure
            // We'll do this in a separate migration to avoid conflicts
            
        } catch (\Exception $e) {
            Log::error('Error during report migration: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This is a one-way migration, as going back would be complex
     * and potentially lose data. The old tables are kept for reference.
     */
    public function down(): void
    {
        // Clear out the migrated data
        DB::table('reports')->truncate();
        DB::table('report_files')->truncate();
    }
}; 