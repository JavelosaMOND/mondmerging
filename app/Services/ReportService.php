<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportFile;
use App\Models\ReportType;
use App\Models\User;
use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportService
{
    protected $reportRepository;
    
    /**
     * Create a new service instance.
     *
     * @param ReportRepository $reportRepository
     */
    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }
    
    /**
     * Create a new report.
     *
     * @param User $user
     * @param array $data
     * @param UploadedFile|null $file
     * @return Report
     */
    public function createReport(User $user, array $data, ?UploadedFile $file = null): Report
    {
        DB::beginTransaction();

        try {
            // Get the report type
            $reportType = ReportType::findOrFail($data['report_type_id']);
            
            // Create the period data based on the report frequency
            $periodData = $this->createPeriodData($reportType->frequency, $data);
            
            // Create the report data
            $reportData = [
                'user_id' => $user->id,
                'report_type_id' => $reportType->id,
                'frequency' => $reportType->frequency,
                'period_data' => $periodData,
                'deadline' => $reportType->deadline,
                'status' => $file ? Report::STATUS_SUBMITTED : Report::STATUS_NO_SUBMISSION,
                'remarks' => $data['remarks'] ?? null,
                'num_of_clean_up_sites' => $data['num_of_clean_up_sites'] ?? null,
                'num_of_participants' => $data['num_of_participants'] ?? null,
                'num_of_barangays' => $data['num_of_barangays'] ?? null,
                'total_volume' => $data['total_volume'] ?? null,
            ];
            
            // Create the report using the repository
            $report = $this->reportRepository->create($reportData);
            
            // Upload the file if provided
            if ($file) {
                $this->uploadFile($report, $file, $user);
            }

            // Clear the cache for this user
            $this->reportRepository->clearCache($user);
            
            DB::commit();
            return $report;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create report: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data,
            ]);
            throw $e;
        }
    }
    
    /**
     * Update an existing report.
     *
     * @param Report $report
     * @param array $data
     * @param UploadedFile|null $file
     * @return Report
     */
    public function updateReport(Report $report, array $data, ?UploadedFile $file = null): Report
    {
        DB::beginTransaction();

        try {
            $updateData = [];
            
            // Update period data if needed
            if (isset($data['report_type_id']) && $data['report_type_id'] != $report->report_type_id) {
                $reportType = ReportType::findOrFail($data['report_type_id']);
                $periodData = $this->createPeriodData($reportType->frequency, $data);
                $updateData['period_data'] = $periodData;
                $updateData['frequency'] = $reportType->frequency;
                $updateData['report_type_id'] = $reportType->id;
            } else {
                // Just update the period data fields
                $periodData = $this->createPeriodData($report->frequency, $data, $report->period_data);
                $updateData['period_data'] = $periodData;
            }
            
            // Update other fields
            if (isset($data['remarks'])) {
                $updateData['remarks'] = $data['remarks'];
            }
            
            if (isset($data['num_of_clean_up_sites'])) {
                $updateData['num_of_clean_up_sites'] = $data['num_of_clean_up_sites'];
            }
            
            if (isset($data['num_of_participants'])) {
                $updateData['num_of_participants'] = $data['num_of_participants'];
            }
            
            if (isset($data['num_of_barangays'])) {
                $updateData['num_of_barangays'] = $data['num_of_barangays'];
            }
            
            if (isset($data['total_volume'])) {
                $updateData['total_volume'] = $data['total_volume'];
            }
            
            // If file is uploaded, update status to submitted
            if ($file) {
                $updateData['status'] = Report::STATUS_SUBMITTED;
                $user = User::findOrFail($report->user_id);
                $this->uploadFile($report, $file, $user);
            } elseif (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            
            // Update the report using the repository
            $this->reportRepository->update($report, $updateData);
            
            // Clear the cache for this user
            $user = User::findOrFail($report->user_id);
            $this->reportRepository->clearCache($user);
            
            DB::commit();
            return $report->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update report: ' . $e->getMessage(), [
                'exception' => $e,
                'report_id' => $report->id,
                'data' => $data,
            ]);
            throw $e;
        }
    }
    
    /**
     * Upload a file for a report.
     *
     * @param Report $report
     * @param UploadedFile $file
     * @param User $user
     * @return ReportFile
     */
    public function uploadFile(Report $report, UploadedFile $file, User $user): ReportFile
    {
        // Generate a unique filename
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedFilename = Str::uuid() . '.' . $extension;
        
        // Determine storage path based on report frequency and user
        $path = "reports/{$user->id}/{$report->frequency}";
        
        // Store the file
        $file->storeAs($path, $storedFilename);
        
        // Create the file record
        return ReportFile::create([
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'reportable_id' => $report->id,
            'reportable_type' => Report::class,
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }
    
    /**
     * Get reports for a specific user.
     *
     * @param User $user
     * @param array $filters
     * @return Collection
     */
    public function getReportsForUser(User $user, array $filters = []): Collection
    {
        return $this->reportRepository->getReportsForUser($user, $filters);
    }
    
    /**
     * Get overdue reports for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getOverdueReportsForUser(User $user): Collection
    {
        return $this->reportRepository->getOverdueReports($user);
    }
    
    /**
     * Get dashboard statistics for a user.
     *
     * @param User $user
     * @return array
     */
    public function getDashboardStats(User $user): array
    {
        return $this->reportRepository->getDashboardStats($user);
    }
    
    /**
     * Create period data based on the report frequency and input data.
     *
     * @param string $frequency
     * @param array $data
     * @param array|null $existingData
     * @return array
     */
    protected function createPeriodData(string $frequency, array $data, ?array $existingData = null): array
    {
        $periodData = $existingData ?? [];
        
        switch ($frequency) {
            case 'weekly':
                if (isset($data['month'])) $periodData['month'] = $data['month'];
                if (isset($data['week_number'])) $periodData['week_number'] = $data['week_number'];
                break;
                
            case 'monthly':
                if (isset($data['month'])) $periodData['month'] = $data['month'];
                if (isset($data['year'])) $periodData['year'] = $data['year'];
                break;
                
            case 'quarterly':
                if (isset($data['quarter'])) $periodData['quarter'] = $data['quarter'];
                if (isset($data['year'])) $periodData['year'] = $data['year'];
                break;
                
            case 'semestral':
                if (isset($data['semester'])) $periodData['semester'] = $data['semester'];
                if (isset($data['year'])) $periodData['year'] = $data['year'];
                break;
                
            case 'annual':
                if (isset($data['year'])) $periodData['year'] = $data['year'];
                break;
        }
        
        return $periodData;
    }
} 