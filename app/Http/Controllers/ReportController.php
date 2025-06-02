<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ReportType, WeeklyReport, MonthlyReport, QuarterlyReport, SemestralReport, AnnualReport};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Show the report submission form.
     */
    public function index(Request $request)
    {
        // Get all submissions from different report types
        $weeklyReports = WeeklyReport::with(['user', 'reportType'])->get();
        $monthlyReports = MonthlyReport::with(['user', 'reportType'])->get();
        $quarterlyReports = QuarterlyReport::with(['user', 'reportType'])->get();
        $semestralReports = SemestralReport::with(['user', 'reportType'])->get();
        $annualReports = AnnualReport::with(['user', 'reportType'])->get();

        // Combine all submissions
        $submissions = collect()
            ->concat($weeklyReports->map(function ($report) {
                $isLate = Carbon::parse($report->created_at)->isAfter($report->reportType->deadline);
                return [
                    'id' => $report->id,
                    'type' => 'weekly',
                    'report_type' => $report->reportType->name,
                    'submitted_by' => $report->user->name,
                    'submitted_at' => $report->created_at,
                    'deadline' => $report->reportType->deadline,
                    'status' => $report->status === 'submitted' 
                        ? ($isLate ? 'submitted (late)' : 'submitted (on time)') 
                        : 'no submission',
                    'is_late' => $isLate,
                    'remarks' => $report->remarks,
                    'file_path' => $report->file_path,
                    'file_name' => basename($report->file_path)
                ];
            }))
            ->concat($monthlyReports->map(function ($report) {
                $isLate = Carbon::parse($report->created_at)->isAfter($report->reportType->deadline);
                return [
                    'id' => $report->id,
                    'type' => 'monthly',
                    'report_type' => $report->reportType->name,
                    'submitted_by' => $report->user->name,
                    'submitted_at' => $report->created_at,
                    'deadline' => $report->reportType->deadline,
                    'status' => $report->status === 'submitted' 
                        ? ($isLate ? 'submitted (late)' : 'submitted (on time)') 
                        : 'no submission',
                    'is_late' => $isLate,
                    'remarks' => $report->remarks,
                    'file_path' => $report->file_path,
                    'file_name' => basename($report->file_path)
                ];
            }))
            ->concat($quarterlyReports->map(function ($report) {
                $isLate = Carbon::parse($report->created_at)->isAfter($report->reportType->deadline);
                return [
                    'id' => $report->id,
                    'type' => 'quarterly',
                    'report_type' => $report->reportType->name,
                    'submitted_by' => $report->user->name,
                    'submitted_at' => $report->created_at,
                    'deadline' => $report->reportType->deadline,
                    'status' => $report->status === 'submitted' 
                        ? ($isLate ? 'submitted (late)' : 'submitted (on time)') 
                        : 'no submission',
                    'is_late' => $isLate,
                    'remarks' => $report->remarks,
                    'file_path' => $report->file_path,
                    'file_name' => basename($report->file_path)
                ];
            }))
            ->concat($semestralReports->map(function ($report) {
                $isLate = Carbon::parse($report->created_at)->isAfter($report->reportType->deadline);
                return [
                    'id' => $report->id,
                    'type' => 'semestral',
                    'report_type' => $report->reportType->name,
                    'submitted_by' => $report->user->name,
                    'submitted_at' => $report->created_at,
                    'deadline' => $report->reportType->deadline,
                    'status' => $report->status === 'submitted' 
                        ? ($isLate ? 'submitted (late)' : 'submitted (on time)') 
                        : 'no submission',
                    'is_late' => $isLate,
                    'remarks' => $report->remarks,
                    'file_path' => $report->file_path,
                    'file_name' => basename($report->file_path)
                ];
            }))
            ->concat($annualReports->map(function ($report) {
                $isLate = Carbon::parse($report->created_at)->isAfter($report->reportType->deadline);
                return [
                    'id' => $report->id,
                    'type' => 'annual',
                    'report_type' => $report->reportType->name,
                    'submitted_by' => $report->user->name,
                    'submitted_at' => $report->created_at,
                    'deadline' => $report->reportType->deadline,
                    'status' => $report->status === 'submitted' 
                        ? ($isLate ? 'submitted (late)' : 'submitted (on time)') 
                        : 'no submission',
                    'is_late' => $isLate,
                    'remarks' => $report->remarks,
                    'file_path' => $report->file_path,
                    'file_name' => basename($report->file_path)
                ];
            }))
            ->sortByDesc('submitted_at');

        // Apply filters if they exist
        if ($request->filled('type')) {
            $submissions = $submissions->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $submissions = $submissions->where('status', $request->status);
        }
        if ($request->filled('timeliness')) {
            $submissions = $submissions->where('is_late', $request->timeliness === 'late');
        }
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $submissions = $submissions->filter(function ($submission) use ($search) {
                return str_contains(strtolower($submission['report_type']), $search) ||
                       str_contains(strtolower($submission['submitted_by']), $search);
            });
        }

        // Convert to pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $total = $submissions->count();
        $submissions = $submissions->forPage($page, $perPage);

        // Create paginator
        $submissions = new \Illuminate\Pagination\LengthAwarePaginator(
            $submissions,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );

        return view('admin.view-submissions', compact('submissions'));
    }

    public function showSubmitReport()
    {
        // Get all report types and ensure we have at least one of each frequency
        $reportTypes = ReportType::all();

        // If no report types exist, create default ones
        if ($reportTypes->isEmpty()) {
            $defaultReportTypes = [
                ['name' => 'Weekly Clean-up Report', 'frequency' => 'weekly', 'deadline' => now()->addWeek()],
                ['name' => 'Monthly Progress Report', 'frequency' => 'monthly', 'deadline' => now()->addMonth()],
                ['name' => 'Quarterly Assessment Report', 'frequency' => 'quarterly', 'deadline' => now()->addMonths(3)],
                ['name' => 'Semestral Evaluation Report', 'frequency' => 'semestral', 'deadline' => now()->addMonths(6)],
                ['name' => 'Annual Summary Report', 'frequency' => 'annual', 'deadline' => now()->addYear()]
            ];

            foreach ($defaultReportTypes as $type) {
                ReportType::create($type);
            }

            $reportTypes = ReportType::all();
        }

        // Get the current user's ID
        $userId = Auth::id();

        // Fetch the submitted reports for the current user
        $weeklyReports = WeeklyReport::where('user_id', $userId)
            ->with('user', 'reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $monthlyReports = MonthlyReport::where('user_id', $userId)
            ->with('user', 'reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $quarterlyReports = QuarterlyReport::where('user_id', $userId)
            ->with('user', 'reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $semestralReports = SemestralReport::where('user_id', $userId)
            ->with('user', 'reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $annualReports = AnnualReport::where('user_id', $userId)
            ->with('user', 'reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get the report type IDs that have already been submitted
        $submittedReportTypeIds = collect();
        $submittedReportTypeIds = $submittedReportTypeIds
            ->merge($weeklyReports->pluck('report_type_id'))
            ->merge($monthlyReports->pluck('report_type_id'))
            ->merge($quarterlyReports->pluck('report_type_id'))
            ->merge($semestralReports->pluck('report_type_id'))
            ->merge($annualReports->pluck('report_type_id'))
            ->unique();

        // Filter out report types that have already been submitted
        $availableReportTypes = $reportTypes->whereNotIn('id', $submittedReportTypeIds);

        $submittedReportsByFrequency = [
            'weekly' => $weeklyReports,
            'monthly' => $monthlyReports,
            'quarterly' => $quarterlyReports,
            'semestral' => $semestralReports,
            'annual' => $annualReports,
        ];

        return view('barangay.submit-report', [
            'reportTypes' => $availableReportTypes,
            'submittedReportsByFrequency' => $submittedReportsByFrequency,
            'submittedReportTypeIds' => $submittedReportTypeIds,
            'allReportTypes' => $reportTypes // Pass all report types to the view
        ]);
    }

    /**
     * Update report remarks.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'remarks' => 'nullable|string|max:1000',
                'type' => 'required|in:weekly,monthly,quarterly,semestral,annual'
            ]);

            // Map report types to their models
            $reportModels = [
                'weekly' => WeeklyReport::class,
                'monthly' => MonthlyReport::class,
                'quarterly' => QuarterlyReport::class,
                'semestral' => SemestralReport::class,
                'annual' => AnnualReport::class
            ];

            $model = $reportModels[$validated['type']];
            $report = $model::findOrFail($id);

            // Only update remarks
            $report->update([
                'remarks' => $validated['remarks']
            ]);

            return redirect()->back()->with('success', 'Report remarks updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update report remarks.');
        }
    }

    /**
     * Show the form for submitting reports.
     */
    public function create()
    {
        $reportTypes = ReportType::all();

        // Fetch the submitted reports
        $weeklyReports = WeeklyReport::with('user', 'reportType')->orderBy('created_at', 'desc')->get();
        $monthlyReports = MonthlyReport::with('user', 'reportType')->orderBy('created_at', 'desc')->get();
        $quarterlyReports = QuarterlyReport::with('user', 'reportType')->orderBy('created_at', 'desc')->get();
        $semestralReports = SemestralReport::with('user', 'reportType')->orderBy('created_at', 'desc')->get();
        $annualReports = AnnualReport::with('user', 'reportType')->orderBy('created_at', 'desc')->get();

        // Get the report type IDs that have already been submitted
        $submittedReportTypeIds = collect();
        $submittedReportTypeIds = $submittedReportTypeIds
            ->merge($weeklyReports->pluck('report_type_id'))
            ->merge($monthlyReports->pluck('report_type_id'))
            ->merge($quarterlyReports->pluck('report_type_id'))
            ->merge($semestralReports->pluck('report_type_id'))
            ->merge($annualReports->pluck('report_type_id'))
            ->unique();

        $submittedReportsByFrequency = [
            'weekly' => $weeklyReports,
            'monthly' => $monthlyReports,
            'quarterly' => $quarterlyReports,
            'semestral' => $semestralReports,
            'annual' => $annualReports,
        ];

        return view('barangay.submit-report', [
            'reportTypes' => $reportTypes,
            'submittedReportsByFrequency' => $submittedReportsByFrequency,
            'allReportTypes' => $reportTypes,
            'submittedReportTypeIds' => $submittedReportTypeIds
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'report_type_id' => 'required|exists:report_types,id',
                'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
                'type' => 'required|in:weekly,monthly,quarterly,semestral,annual'
            ]);

            // Get the report type
            $reportType = ReportType::findOrFail($validated['report_type_id']);

            // Store the file
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('reports', $fileName, 'public');

            // Map report types to their models
            $reportModels = [
                'weekly' => WeeklyReport::class,
                'monthly' => MonthlyReport::class,
                'quarterly' => QuarterlyReport::class,
                'semestral' => SemestralReport::class,
                'annual' => AnnualReport::class
            ];

            // Create the report
            $model = $reportModels[$validated['type']];

            // Prepare base data
            $reportData = [
                'user_id' => Auth::id(),
                'report_type_id' => $validated['report_type_id'],
                'file_path' => $filePath,
                'file_name' => $fileName,
                'deadline' => $reportType->deadline,
                'status' => 'submitted',
                'remarks' => null
            ];

            // Add frequency-specific data
            switch ($validated['type']) {
                case 'weekly':
                    $reportData['month'] = now()->format('F');
                    $reportData['week_number'] = now()->weekOfMonth;
                    $reportData['num_of_clean_up_sites'] = 0;
                    $reportData['num_of_participants'] = 0;
                    $reportData['num_of_barangays'] = 0;
                    $reportData['total_volume'] = 0;
                    break;
                case 'monthly':
                    $reportData['month'] = now()->format('F');
                    $reportData['year'] = now()->year;
                    break;
                case 'quarterly':
                    $reportData['quarter_number'] = ceil(now()->month / 3);
                    $reportData['year'] = now()->year;
                    break;
                case 'semestral':
                    $reportData['semester'] = now()->month <= 6 ? 1 : 2;
                    $reportData['year'] = now()->year;
                    break;
                case 'annual':
                    $reportData['year'] = now()->year;
                    break;
            }

            $report = $model::create($reportData);

            return redirect()->route('barangay.submit.report')
                ->with('success', 'Report submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Error submitting report: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit report. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the view reports page.
     */
    public function view()
    {
        $userId = Auth::id();
        $reportTypes = ReportType::all();

        // Fetch all submitted reports for the current user
        $weeklyReports = WeeklyReport::where('user_id', $userId)
            ->with('reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $monthlyReports = MonthlyReport::where('user_id', $userId)
            ->with('reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $quarterlyReports = QuarterlyReport::where('user_id', $userId)
            ->with('reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $semestralReports = SemestralReport::where('user_id', $userId)
            ->with('reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        $annualReports = AnnualReport::where('user_id', $userId)
            ->with('reportType')
            ->orderBy('created_at', 'desc')
            ->get();

        // Combine all reports into a single collection
        $allReports = collect()
            ->concat($weeklyReports)
            ->concat($monthlyReports)
            ->concat($quarterlyReports)
            ->concat($semestralReports)
            ->concat($annualReports)
            ->sortByDesc('created_at');

        return view('barangay.view-reports', [
            'reports' => $allReports,
            'reportTypes' => $reportTypes
        ]);
    }

    /**
     * Show the resubmit form for a specific report.
     */
    public function showResubmit($id)
    {
        $report = $this->findReport($id);
        if (!$report) {
            return back()->with('error', 'Report not found.');
        }

        $reportTypes = ReportType::all();
        return view('barangay.resubmit-report', compact('report', 'reportTypes'));
    }

    /**
     * Handle report resubmission.
     */
    public function resubmit(Request $request, $id)
    {
        $report = $this->findReport($id);
        if (!$report) {
            return back()->with('error', 'Report not found.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx|max:2048',
        ]);

        $fileName = time() . '_' . str_replace([' ', '(', ')'], '_', $request->file('file')->getClientOriginalName());
        $filePath = "reports/{$report->reportType->frequency}/{$fileName}";

        DB::beginTransaction();
        try {
            // Store file
            Storage::disk('public')->putFileAs(
                "reports/{$report->reportType->frequency}",
                $request->file('file'),
                $fileName
            );

            // Update report
            $report->update([
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_path' => $filePath,
                'status' => 'submitted',
                'remarks' => null,
            ]);

            DB::commit();
            return redirect()->route('reports.view')->with('success', 'Report resubmitted successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to resubmit report: ' . $e->getMessage());
        }
    }

    /**
     * Delete a report.
     */
    public function destroy($id)
    {
        $report = $this->findReport($id);
        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found.']);
        }

        DB::beginTransaction();
        try {
            // Delete the file
            Storage::disk('public')->delete($report->file_path);

            // Delete the report
            $report->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Report deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to delete report: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper method to find a report by ID.
     */
    private function findReport($id)
    {
        $models = [
            WeeklyReport::class,
            MonthlyReport::class,
            QuarterlyReport::class,
            SemestralReport::class,
            AnnualReport::class
        ];

        foreach ($models as $model) {
            $report = $model::find($id);
            if ($report) {
                return $report;
            }
        }

        return null;
    }

    public function overdueReports()
    {
        $reportTypes = ReportType::all();
        $overdueReports = collect();

        foreach ($reportTypes as $reportType) {
            $deadline = Carbon::parse($reportType->deadline);
            if ($deadline->isPast()) {
                $overdueReports->push($reportType);
            }
        }

        return view('barangay.overdue-reports', [
            'overdueReports' => $overdueReports
        ]);
    }

    /**
     * Download a report file.
     */
    public function downloadFile($id)
    {
        try {
            \Log::info('Attempting to find report with ID: ' . $id);

            // Try to find the report in each table
            $report = null;
            $reportTypes = [
                'weekly' => WeeklyReport::class,
                'monthly' => MonthlyReport::class,
                'quarterly' => QuarterlyReport::class,
                'semestral' => SemestralReport::class,
                'annual' => AnnualReport::class
            ];

            foreach ($reportTypes as $type => $model) {
                \Log::info("Checking {$type} reports table");
                $foundReport = $model::find($id);
                if ($foundReport) {
                    $report = $foundReport;
                    \Log::info("Found report in {$type} reports table");
                    break;
                }
            }

            if (!$report) {
                \Log::error('Report not found in any table for ID: ' . $id);
                return response()->json(['error' => 'Report not found.'], 404);
            }

            // Log the file path from the database
            \Log::info('File path from database: ' . $report->file_path);

            // Check if the file exists in storage
            if (!Storage::disk('public')->exists($report->file_path)) {
                \Log::error('File not found in storage: ' . $report->file_path);
                return response()->json(['error' => 'File not found in storage.'], 404);
            }

            // Get the full path to the file
            $path = Storage::disk('public')->path($report->file_path);
            \Log::info('Full file path: ' . $path);

            // Get the file's mime type
            $mimeType = mime_content_type($path);
            $fileName = basename($report->file_path);

            \Log::info('File details:', [
                'path' => $path,
                'mime_type' => $mimeType,
                'file_name' => $fileName,
                'is_download' => request()->has('download')
            ]);

            // If it's a download request, force download
            if (request()->has('download')) {
                return response()->download($path, $fileName);
            }

            // For viewing, determine if the file type is viewable
            $viewableTypes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/gif',
                'text/plain',
                'text/html',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            // Get file extension
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Map common extensions to mime types if mime_content_type fails
            $extensionMimeTypes = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'txt' => 'text/plain',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            // If mime_content_type fails, try to determine from extension
            if (!$mimeType && isset($extensionMimeTypes[$extension])) {
                $mimeType = $extensionMimeTypes[$extension];
                \Log::info('Using mime type from extension: ' . $mimeType);
            }

            if (in_array($mimeType, $viewableTypes)) {
                \Log::info('Serving file as viewable type: ' . $mimeType);
                return response()->file($path, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    'Cache-Control' => 'public, max-age=0',
                    'Accept-Ranges' => 'bytes'
                ]);
            }

            \Log::info('File type not viewable, forcing download: ' . $mimeType);
            // For non-viewable types, force download
            return response()->download($path, $fileName);
        } catch (\Exception $e) {
            \Log::error('File access error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error accessing file: ' . $e->getMessage()], 500);
        }
    }
}
