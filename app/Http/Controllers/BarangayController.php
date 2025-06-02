<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{WeeklyReport, MonthlyReport, QuarterlyReport, SemestralReport, AnnualReport, ReportType};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BarangayController extends Controller
{
    public function __construct()
    {
        if (!Auth::check() || Auth::user()->role !== 'barangay') {
            abort(403, 'Unauthorized access.');
        }
    }

    public function dashboard()
    {
        try {
            $userId = Auth::id();

            // Get all reports for the current user with their relationships
            $weeklyReports = WeeklyReport::with('reportType')
                ->where('user_id', $userId)
                ->get();

            $monthlyReports = MonthlyReport::with('reportType')
                ->where('user_id', $userId)
                ->get();

            $quarterlyReports = QuarterlyReport::with('reportType')
                ->where('user_id', $userId)
                ->get();

            $semestralReports = SemestralReport::with('reportType')
                ->where('user_id', $userId)
                ->get();

            $annualReports = AnnualReport::with('reportType')
                ->where('user_id', $userId)
                ->get();

            // Get all report types
            $allReportTypes = ReportType::orderBy('name')->get();

            // Combine all reports
            $allReports = collect()
                ->concat($weeklyReports)
                ->concat($monthlyReports)
                ->concat($quarterlyReports)
                ->concat($semestralReports)
                ->concat($annualReports);

            // Calculate statistics
            $totalReports = $allReports->count();
            $submittedReports = $allReports->where('status', 'submitted')->count();
            $noSubmissionReports = $allReports->where('status', 'no submission')->count();

            // Get recent reports (last 5)
            $recentReports = $allReports
                ->sortByDesc('created_at')
                ->take(5);

            // Get submitted report type IDs
            $submittedReportTypeIds = collect()
                ->merge($weeklyReports->pluck('report_type_id'))
                ->merge($monthlyReports->pluck('report_type_id'))
                ->merge($quarterlyReports->pluck('report_type_id'))
                ->merge($semestralReports->pluck('report_type_id'))
                ->merge($annualReports->pluck('report_type_id'))
                ->unique();

            // Log the submitted report type IDs for debugging
            Log::info('Submitted report type IDs:', ['ids' => $submittedReportTypeIds->toArray()]);

            // Get all report types
            $allReportTypes = ReportType::all();

            // Get upcoming deadlines and ensure they are Carbon instances
            // Include all report types with deadlines in the future, regardless of submission status
            $upcomingDeadlines = ReportType::where('deadline', '>=', now())
                ->orderBy('deadline')
                ->take(20)
                ->get()
                ->map(function ($reportType) {
                    $reportType->deadline = \Carbon\Carbon::parse($reportType->deadline);
                    return $reportType;
                });

            // Filter out already submitted report types
            $upcomingDeadlines = $upcomingDeadlines->filter(function ($reportType) use ($submittedReportTypeIds) {
                return !$submittedReportTypeIds->contains($reportType->id);
            })->take(10);

            // Filter out already submitted report types for the dropdown menu
            $reportTypes = ReportType::whereNotIn('id', $submittedReportTypeIds)
                ->orderBy('name')
                ->get();

            // Group report types by frequency for easier filtering in the frontend
            $reportTypesByFrequency = [
                'weekly' => $reportTypes->where('frequency', 'weekly'),
                'monthly' => $reportTypes->where('frequency', 'monthly'),
                'quarterly' => $reportTypes->where('frequency', 'quarterly'),
                'semestral' => $reportTypes->where('frequency', 'semestral'),
                'annual' => $reportTypes->where('frequency', 'annual'),
            ];

            // Count available report types by frequency
            $availableReportTypeCounts = [
                'weekly' => $reportTypesByFrequency['weekly']->count(),
                'monthly' => $reportTypesByFrequency['monthly']->count(),
                'quarterly' => $reportTypesByFrequency['quarterly']->count(),
                'semestral' => $reportTypesByFrequency['semestral']->count(),
                'annual' => $reportTypesByFrequency['annual']->count(),
                'total' => $reportTypes->count()
            ];

            Log::info('Available report type counts:', $availableReportTypeCounts);

            return view('barangay.dashboard', compact(
                'totalReports',
                'submittedReports',
                'noSubmissionReports',
                'recentReports',
                'upcomingDeadlines',
                'reportTypes',
                'reportTypesByFrequency',
                'submittedReportTypeIds',
                'allReportTypes',
                'availableReportTypeCounts'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the dashboard: ' . $e->getMessage());
        }
    }

    public function viewReports()
    {
        $userId = Auth::id();
        $perPage = request()->get('per_page', 10);

        // Get all reports for the current user
        $weeklyReports = WeeklyReport::with('reportType')
            ->where('user_id', $userId)
            ->get();

        $monthlyReports = MonthlyReport::with('reportType')
            ->where('user_id', $userId)
            ->get();

        $quarterlyReports = QuarterlyReport::with('reportType')
            ->where('user_id', $userId)
            ->get();

        $semestralReports = SemestralReport::with('reportType')
            ->where('user_id', $userId)
            ->get();

        $annualReports = AnnualReport::with('reportType')
            ->where('user_id', $userId)
            ->get();

        // Combine all reports
        $reports = collect()
            ->concat($weeklyReports)
            ->concat($monthlyReports)
            ->concat($quarterlyReports)
            ->concat($semestralReports)
            ->concat($annualReports)
            ->sortByDesc('created_at');

        // Create a new paginator instance
        $page = request()->get('page', 1);
        $reports = new \Illuminate\Pagination\LengthAwarePaginator(
            $reports->forPage($page, $perPage),
            $reports->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return view('barangay.view-reports', compact('reports'));
    }

    public function overdueReports()
    {
        try {
            $userId = Auth::id();
            $perPage = request()->get('per_page', 10);

            Log::info('Fetching overdue reports for user ID: ' . $userId);

            // Get all report types that are past their deadline
            $overdueReportTypes = ReportType::where('deadline', '<', now())
                ->orderBy('deadline', 'desc')
                ->get();

            Log::info('Found ' . $overdueReportTypes->count() . ' report types with past deadlines');

            // Get all submitted reports for the current user
            $weeklyReports = WeeklyReport::where('user_id', $userId)->pluck('report_type_id');
            $monthlyReports = MonthlyReport::where('user_id', $userId)->pluck('report_type_id');
            $quarterlyReports = QuarterlyReport::where('user_id', $userId)->pluck('report_type_id');
            $semestralReports = SemestralReport::where('user_id', $userId)->pluck('report_type_id');
            $annualReports = AnnualReport::where('user_id', $userId)->pluck('report_type_id');

            // Combine all submitted report type IDs
            $submittedReportTypeIds = collect()
                ->concat($weeklyReports)
                ->concat($monthlyReports)
                ->concat($quarterlyReports)
                ->concat($semestralReports)
                ->concat($annualReports)
                ->unique();

            Log::info('User has submitted ' . $submittedReportTypeIds->count() . ' report types', [
                'submitted_ids' => $submittedReportTypeIds->toArray()
            ]);

            // Filter out report types that have already been submitted
            $overdueReports = $overdueReportTypes->filter(function ($reportType) use ($submittedReportTypeIds) {
                return !$submittedReportTypeIds->contains($reportType->id);
            });

            Log::info('After filtering, found ' . $overdueReports->count() . ' overdue reports that need submission');

            // Create a new paginator instance
            $page = request()->get('page', 1);
            $reports = new \Illuminate\Pagination\LengthAwarePaginator(
                $overdueReports->forPage($page, $perPage),
                $overdueReports->count(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query()
                ]
            );

            return view('barangay.overdue-reports', compact('reports'));
        } catch (\Exception $e) {
            Log::error('Error in overdueReports method: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return view('barangay.overdue-reports', ['reports' => collect()]);
        }
    }

    public function submissions(Request $request)
    {
        $userId = Auth::id();
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $frequency = $request->get('frequency', '');
        $sortBy = $request->get('sort_by', 'newest');

        try {
            // Get all reports for the current user with their relationships
            $weeklyReports = WeeklyReport::with('reportType')
                ->where('user_id', $userId)
                ->get()
                ->map(function ($report) {
                    $report->model_type = 'WeeklyReport';
                    // Add a unique identifier that includes the table name and ID
                    $report->unique_id = 'weekly_' . $report->id;
                    return $report;
                });

            $monthlyReports = MonthlyReport::with('reportType')
                ->where('user_id', $userId)
                ->get()
                ->map(function ($report) {
                    $report->model_type = 'MonthlyReport';
                    // Add a unique identifier that includes the table name and ID
                    $report->unique_id = 'monthly_' . $report->id;
                    return $report;
                });

            $quarterlyReports = QuarterlyReport::with('reportType')
                ->where('user_id', $userId)
                ->get()
                ->map(function ($report) {
                    $report->model_type = 'QuarterlyReport';
                    // Add a unique identifier that includes the table name and ID
                    $report->unique_id = 'quarterly_' . $report->id;
                    return $report;
                });

            $semestralReports = SemestralReport::with('reportType')
                ->where('user_id', $userId)
                ->get()
                ->map(function ($report) {
                    $report->model_type = 'SemestralReport';
                    // Add a unique identifier that includes the table name and ID
                    $report->unique_id = 'semestral_' . $report->id;
                    return $report;
                });

            $annualReports = AnnualReport::with('reportType')
                ->where('user_id', $userId)
                ->get()
                ->map(function ($report) {
                    $report->model_type = 'AnnualReport';
                    // Add a unique identifier that includes the table name and ID
                    $report->unique_id = 'annual_' . $report->id;
                    return $report;
                });

            // Combine all reports
            $allReports = collect()
                ->concat($weeklyReports)
                ->concat($monthlyReports)
                ->concat($quarterlyReports)
                ->concat($semestralReports)
                ->concat($annualReports);

            // Group reports by report_type_id and get only the latest submission for each type
            $latestReports = collect();
            $groupedReports = $allReports->groupBy('report_type_id');

            foreach ($groupedReports as $reportTypeId => $reportsGroup) {
                // Sort by created_at in descending order and take the first one (latest)
                $latestReport = $reportsGroup->sortByDesc('created_at')->first();
                if ($latestReport) {
                    $latestReports->push($latestReport);
                }
            }

            // Use the filtered collection of latest reports
            $reports = $latestReports;

            // Apply search filter if provided
            if (!empty($search)) {
                $search = strtolower($search);
                $reports = $reports->filter(function ($report) use ($search) {
                    // Search in report type name
                    if (stripos($report->reportType->name, $search) !== false) {
                        return true;
                    }

                    // Search in status
                    if (stripos($report->status, $search) !== false) {
                        return true;
                    }

                    // Search in frequency
                    if (stripos($report->reportType->frequency, $search) !== false) {
                        return true;
                    }

                    // Search in file name if available
                    if ($report->file_name && stripos($report->file_name, $search) !== false) {
                        return true;
                    }

                    return false;
                });
            }

            // Apply frequency filter if provided
            if (!empty($frequency)) {
                $reports = $reports->filter(function ($report) use ($frequency) {
                    return strtolower($report->reportType->frequency) === strtolower($frequency);
                });
            }

            // Apply sorting
            switch ($sortBy) {
                case 'oldest':
                    $reports = $reports->sortBy('created_at');
                    break;
                case 'type':
                    $reports = $reports->sortBy(function ($report) {
                        return $report->reportType->name;
                    });
                    break;
                case 'status':
                    $reports = $reports->sortBy('status');
                    break;
                case 'newest':
                default:
                    $reports = $reports->sortByDesc('created_at');
                    break;
            }

            // Create a new paginator instance
            $page = $request->get('page', 1);

            // Only create a paginator if there are reports
            if ($reports->count() > 0) {
                $reports = new \Illuminate\Pagination\LengthAwarePaginator(
                    $reports->forPage($page, $perPage),
                    $reports->count(),
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'query' => $request->query()
                    ]
                );
            } else {
                // Create an empty paginator
                $reports = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(),
                    0,
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'query' => $request->query()
                    ]
                );
            }

            return view('barangay.submissions', compact('reports', 'search', 'frequency', 'sortBy'));
        } catch (\Exception $e) {
            Log::error('Error in submissions method: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return view('barangay.submissions', ['reports' => collect()]);
        }
    }

    public function submitReport()
    {
        try {
            $userId = Auth::id();
            $allReportTypes = ReportType::all();

            // Get all reports for the current user
            $weeklyReports = WeeklyReport::where('user_id', $userId)->get();
            $monthlyReports = MonthlyReport::where('user_id', $userId)->get();
            $quarterlyReports = QuarterlyReport::where('user_id', $userId)->get();
            $semestralReports = SemestralReport::where('user_id', $userId)->get();
            $annualReports = AnnualReport::where('user_id', $userId)->get();

            // Get the report type IDs that have already been submitted
            $submittedReportTypeIds = collect();
            $submittedReportTypeIds = $submittedReportTypeIds
                ->merge($weeklyReports->pluck('report_type_id'))
                ->merge($monthlyReports->pluck('report_type_id'))
                ->merge($quarterlyReports->pluck('report_type_id'))
                ->merge($semestralReports->pluck('report_type_id'))
                ->merge($annualReports->pluck('report_type_id'))
                ->unique();

            // Log the submitted report type IDs for debugging
            Log::info('Submit Report - Submitted report type IDs:', ['ids' => $submittedReportTypeIds->toArray()]);

            // Get all report types (allowing resubmission)
            $reportTypes = ReportType::orderBy('name')->get();

            // Group report types by frequency for easier filtering in the frontend
            $reportTypesByFrequency = [
                'weekly' => $reportTypes->where('frequency', 'weekly'),
                'monthly' => $reportTypes->where('frequency', 'monthly'),
                'quarterly' => $reportTypes->where('frequency', 'quarterly'),
                'semestral' => $reportTypes->where('frequency', 'semestral'),
                'annual' => $reportTypes->where('frequency', 'annual'),
            ];

            // Count available report types by frequency
            $availableReportTypeCounts = [
                'weekly' => $reportTypesByFrequency['weekly']->count(),
                'monthly' => $reportTypesByFrequency['monthly']->count(),
                'quarterly' => $reportTypesByFrequency['quarterly']->count(),
                'semestral' => $reportTypesByFrequency['semestral']->count(),
                'annual' => $reportTypesByFrequency['annual']->count(),
                'total' => $reportTypes->count()
            ];

            Log::info('Submit Report - Available report type counts:', $availableReportTypeCounts);

            // Organize submitted reports by frequency
            $submittedReportsByFrequency = [
                'weekly' => $weeklyReports,
                'monthly' => $monthlyReports,
                'quarterly' => $quarterlyReports,
                'semestral' => $semestralReports,
                'annual' => $annualReports
            ];

            return view('barangay.submit-report', compact(
                'reportTypes',
                'allReportTypes',
                'submittedReportTypeIds',
                'submittedReportsByFrequency',
                'reportTypesByFrequency',
                'availableReportTypeCounts'
            ));
        } catch (\Exception $e) {
            Log::error('Error in submitReport method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while loading the submit report page: ' . $e->getMessage());
        }
    }

    public function storeFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx|max:2048',
            'report_id' => 'required|exists:reports,id',
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('reports', $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'filename' => $filename
            ]);
        } catch (\Exception $exception) {
            Log::error('File upload error: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $exception->getMessage()
            ], 500);
        }
    }

    public function downloadFile($id)
    {
        try {
            // Parse the unique identifier to get the table name and ID
            $parts = explode('_', $id);

            if (count($parts) < 2) {
                // If the ID doesn't contain an underscore, it's an old-style ID
                // In this case, we'll try to find the report in each table
                $reportId = $id;
                $reportTable = null;
            } else {
                // Extract the table name and ID from the unique identifier
                $reportTable = $parts[0];
                $reportId = $parts[1];
            }

            Log::info('Parsed report ID for download: ' . $reportId . ', Table: ' . ($reportTable ?? 'unknown'));

            $report = null;

            // If we know the table, we can directly query that table
            if ($reportTable) {
                switch ($reportTable) {
                    case 'weekly':
                        $report = WeeklyReport::where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();
                        break;

                    case 'monthly':
                        $report = MonthlyReport::where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();
                        break;

                    case 'quarterly':
                        $report = QuarterlyReport::where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();
                        break;

                    case 'semestral':
                        $report = SemestralReport::where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();
                        break;

                    case 'annual':
                        $report = AnnualReport::where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();
                        break;
                }
            } else {
                // If we don't know the table, we need to check all tables
                // Try to find the report in each table
                $report = WeeklyReport::where('id', $reportId)
                    ->where('user_id', Auth::id())
                    ->first();

                if (!$report) {
                    $report = MonthlyReport::where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();
                }

                if (!$report) {
                    $report = QuarterlyReport::where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();
                }

                if (!$report) {
                    $report = SemestralReport::where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();
                }

                if (!$report) {
                    $report = AnnualReport::where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();
                }
            }

            if (!$report) {
                abort(404, 'Report not found');
            }

            $path = storage_path('app/public/' . $report->file_path);

            if (!file_exists($path)) {
                abort(404, 'File not found');
            }

            return response()->download($path, $report->file_name);
        } catch (\Exception $e) {
            Log::error('File download error: ' . $e->getMessage());
            return back()->with('error', 'Failed to download file. Please try again.');
        }
    }

    public function viewFile($id)
    {
        try {
            // Try to find the report in each table
            $report = WeeklyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$report) {
                $report = MonthlyReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
            }

            if (!$report) {
                $report = QuarterlyReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
            }

            if (!$report) {
                $report = SemestralReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
            }

            if (!$report) {
                $report = AnnualReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
            }

            if (!$report) {
                abort(404, 'Report not found');
            }

            $path = storage_path('app/public/' . $report->file_path);

            if (!file_exists($path)) {
                abort(404, 'File not found');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            Log::error('File view error: ' . $e->getMessage());
            return back()->with('error', 'Failed to view file. Please try again.');
        }
    }

    public function deleteFile($id)
    {
        // Try to find the report in each table
        $report = WeeklyReport::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            $report = MonthlyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = QuarterlyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = SemestralReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            $report = AnnualReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$report) {
            abort(404, 'Report not found');
        }

        if ($report->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        Storage::delete('public/' . $report->file_path);
        $report->update(['file_path' => null, 'file_name' => null]);

        return back()->with('success', 'File deleted successfully');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get the report type
            $reportType = ReportType::findOrFail($request->report_type_id);

            // Validate based on report type
            $validationRules = [
                'report_type_id' => 'required|exists:report_types,id',
                'file' => 'required|file|mimes:pdf,doc,docx,xlsx|max:2048'
            ];

            // Add validation rules based on report type
            switch ($reportType->frequency) {
                case 'weekly':
                    $validationRules = array_merge($validationRules, [
                        'num_of_clean_up_sites' => 'required|integer|min:0',
                        'num_of_participants' => 'required|integer|min:0',
                        'num_of_barangays' => 'required|integer|min:0',
                        'total_volume' => 'required|numeric|min:0'
                    ]);
                    break;
                case 'monthly':
                    $validationRules = array_merge($validationRules, [
                        'month' => 'required|string|in:January,February,March,April,May,June,July,August,September,October,November,December'
                    ]);
                    break;
                case 'quarterly':
                    $validationRules = array_merge($validationRules, [
                        'quarter_number' => 'required|integer|in:1,2,3,4'
                    ]);
                    break;
                case 'semestral':
                    $validationRules = array_merge($validationRules, [
                        'sem_number' => 'required|integer|in:1,2'
                    ]);
                    break;
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Store the file
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('reports', $fileName, 'public');

            // Create the appropriate report based on frequency
            $report = null;
            switch ($reportType->frequency) {
                case 'weekly':
                    // Create a new report entry (allowing multiple submissions)
                    $report = WeeklyReport::create([
                        'user_id' => Auth::id(),
                        'report_type_id' => $request->report_type_id,
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'status' => 'submitted',
                        'deadline' => $reportType->deadline,
                        'month' => $request->month,
                        'week_number' => $request->week_number,
                        'num_of_clean_up_sites' => $request->num_of_clean_up_sites,
                        'num_of_participants' => $request->num_of_participants,
                        'num_of_barangays' => $request->num_of_barangays,
                        'total_volume' => $request->total_volume
                    ]);
                    break;

                case 'monthly':
                    // Create a new report entry (allowing multiple submissions)
                    $report = MonthlyReport::create([
                        'user_id' => Auth::id(),
                        'report_type_id' => $request->report_type_id,
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'status' => 'submitted',
                        'deadline' => $reportType->deadline,
                        'month' => $request->month
                    ]);
                    break;

                case 'quarterly':
                    // Create a new report entry (allowing multiple submissions)
                    $report = QuarterlyReport::create([
                        'user_id' => Auth::id(),
                        'report_type_id' => $request->report_type_id,
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'status' => 'submitted',
                        'deadline' => $reportType->deadline,
                        'quarter_number' => $request->quarter_number
                    ]);
                    break;

                case 'semestral':
                    // Create a new report entry (allowing multiple submissions)
                    $report = SemestralReport::create([
                        'user_id' => Auth::id(),
                        'report_type_id' => $request->report_type_id,
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'status' => 'submitted',
                        'deadline' => $reportType->deadline,
                        'sem_number' => $request->input('sem_number', 1)
                    ]);
                    break;

                case 'annual':
                    // Create a new report entry (allowing multiple submissions)
                    $report = AnnualReport::create([
                        'user_id' => Auth::id(),
                        'report_type_id' => $request->report_type_id,
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'status' => 'submitted',
                        'deadline' => $reportType->deadline
                    ]);
                    break;
            }

            if (!$report) {
                throw new \Exception('Failed to create report record.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Report submission error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report. Please try again. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resubmit(Request $request, $id)
    {
        try {
            // Log the request data for debugging
            Log::info('Resubmit request data:', $request->all());
            Log::info('Report ID being resubmitted: ' . $id);

            // Log the request method and headers
            Log::info('Request method: ' . $request->method());
            Log::info('Request headers:', $request->headers->all());

            // Log the form data
            Log::info('Form data:', $request->post());

            // Log the files
            if ($request->hasFile('file')) {
                Log::info('File uploaded: ' . $request->file('file')->getClientOriginalName());
            } else {
                Log::info('No file uploaded');
            }

            // Debug response - commented out to allow the function to continue
            // return response()->json(['success' => true, 'message' => 'Debug: Controller method reached', 'data' => $request->all()]);


            // Parse the unique identifier to get the table name and ID
            $parts = explode('_', $id);

            if (count($parts) < 2) {
                // If the ID doesn't contain an underscore, it's an old-style ID
                // In this case, we'll try to find the report in each table
                $reportId = $id;
                $reportTable = null;
            } else {
                // Extract the table name and ID from the unique identifier
                $reportTable = $parts[0];
                $reportId = $parts[1];
            }

            Log::info('Parsed report ID: ' . $reportId . ', Table: ' . ($reportTable ?? 'unknown'));
        } catch (\Exception $e) {
            Log::error('Error in resubmit method: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing your request. Please try again.');
        }

        // Find the report first to determine its type
        $report = null;
        $reportType = null;
        $reportModel = null;

        try {
            // If we know the table, we can directly query that table
            if ($reportTable) {
                switch ($reportTable) {
                    case 'weekly':
                        $report = WeeklyReport::with('reportType')
                            ->where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();

                        if ($report) {
                            $reportType = 'weekly';
                            $reportModel = 'WeeklyReport';
                            Log::info('Found report in WeeklyReport table', ['id' => $report->id, 'type' => $reportType]);
                        }
                        break;

                    case 'monthly':
                        $report = MonthlyReport::with('reportType')
                            ->where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();

                        if ($report) {
                            $reportType = 'monthly';
                            $reportModel = 'MonthlyReport';
                            Log::info('Found report in MonthlyReport table', ['id' => $report->id, 'type' => $reportType]);
                        }
                        break;

                    case 'quarterly':
                        $report = QuarterlyReport::with('reportType')
                            ->where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();

                        if ($report) {
                            $reportType = 'quarterly';
                            $reportModel = 'QuarterlyReport';
                            Log::info('Found report in QuarterlyReport table', ['id' => $report->id, 'type' => $reportType]);
                        }
                        break;

                    case 'semestral':
                        $report = SemestralReport::with('reportType')
                            ->where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();

                        if ($report) {
                            $reportType = 'semestral';
                            $reportModel = 'SemestralReport';
                            Log::info('Found report in SemestralReport table', ['id' => $report->id, 'type' => $reportType]);
                        }
                        break;

                    case 'annual':
                        $report = AnnualReport::with('reportType')
                            ->where('id', $reportId)
                            ->where('user_id', Auth::id())
                            ->first();

                        if ($report) {
                            $reportType = 'annual';
                            $reportModel = 'AnnualReport';
                            Log::info('Found report in AnnualReport table', ['id' => $report->id, 'type' => $reportType]);
                        }
                        break;
                }
            } else {
                // If we don't know the table, we need to check all tables
                // Try to find the report in each table
                $report = WeeklyReport::with('reportType')
                    ->where('id', $reportId)
                    ->where('user_id', Auth::id())
                    ->first();

                if ($report) {
                    $reportType = 'weekly';
                    $reportModel = 'WeeklyReport';
                    Log::info('Found report in WeeklyReport table', ['id' => $report->id, 'type' => $reportType]);
                }

                if (!$report) {
                    $report = MonthlyReport::with('reportType')
                        ->where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($report) {
                        $reportType = 'monthly';
                        $reportModel = 'MonthlyReport';
                        Log::info('Found report in MonthlyReport table', ['id' => $report->id, 'type' => $reportType]);
                    }
                }

                if (!$report) {
                    $report = QuarterlyReport::with('reportType')
                        ->where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($report) {
                        $reportType = 'quarterly';
                        $reportModel = 'QuarterlyReport';
                        Log::info('Found report in QuarterlyReport table', ['id' => $report->id, 'type' => $reportType]);
                    }
                }

                if (!$report) {
                    $report = SemestralReport::with('reportType')
                        ->where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($report) {
                        $reportType = 'semestral';
                        $reportModel = 'SemestralReport';
                        Log::info('Found report in SemestralReport table', ['id' => $report->id, 'type' => $reportType]);
                    }
                }

                if (!$report) {
                    $report = AnnualReport::with('reportType')
                        ->where('id', $reportId)
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($report) {
                        $reportType = 'annual';
                        $reportModel = 'AnnualReport';
                        Log::info('Found report in AnnualReport table', ['id' => $report->id, 'type' => $reportType]);
                    }
                }
            }

            // If no report is found, return with an error message
            if (!$report) {
                Log::warning("No report found with ID: {$reportId} for user: " . Auth::id());
                return back()->with('error', 'Report not found. It may have been deleted or you do not have permission to access it.');
            }
        } catch (\Exception $e) {
            Log::error('Error finding report: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while retrieving the report. Please try again.');
        }

        try {
            // Log the report type and model for debugging
            Log::info("Found report: ID={$reportId}, Type={$reportType}, Model={$reportModel}");

            // Log the report data for debugging
            Log::info("Report data:", $report->toArray());

            // Override the report type from the request with the actual report type
            // Ensure report type is lowercase for consistent comparison
            $reportType = strtolower($reportType);
            $request->merge(['report_type' => $reportType]);

            // Log the normalized report type
            Log::info("Normalized report type: {$reportType}");
        } catch (\Exception $e) {
            Log::error('Error processing report data: ' . $e->getMessage());
            // Continue execution as this is just logging
        }

        // Basic validation for file - make file optional for resubmission
        $validationRules = [
            'file' => 'nullable|file|mimes:pdf,doc,docx,xlsx|max:2048',
            'report_type_id' => 'required|exists:report_types,id',
            'report_type' => 'required|string|in:weekly,monthly,quarterly,semestral,annual'
        ];

        // Add specific validation rules based on report type
        // Use strtolower for case-insensitive comparison
        $reportTypeLC = strtolower($request->report_type);

        if ($reportTypeLC == 'weekly') {
            $validationRules = array_merge($validationRules, [
                'month' => 'required|string',
                'week_number' => 'required|integer|min:1|max:52',
                'num_of_clean_up_sites' => 'required|integer|min:0',
                'num_of_participants' => 'required|integer|min:0',
                'num_of_barangays' => 'required|integer|min:0',
                'total_volume' => 'required|numeric|min:0'
            ]);
        } elseif ($reportTypeLC == 'monthly') {
            $validationRules = array_merge($validationRules, [
                'month' => 'required|string'
            ]);
        } elseif ($reportTypeLC == 'quarterly') {
            $validationRules = array_merge($validationRules, [
                'quarter_number' => 'required|integer|in:1,2,3,4'
            ]);
        } elseif ($reportTypeLC == 'semestral') {
            $validationRules = array_merge($validationRules, [
                'sem_number' => 'required|integer|in:1,2'
            ]);
        } elseif ($reportTypeLC == 'annual') {
            // No additional validation rules needed for annual reports
        }

        // Filter the request data to only include fields relevant to the report type
        $filteredData = $request->only(['file', 'report_type_id', 'report_type']);

        // Add specific fields based on report type
        // Use the same reportTypeLC variable for consistency
        if ($reportTypeLC == 'weekly') {
            $filteredData = array_merge($filteredData, $request->only([
                'month', 'week_number', 'num_of_clean_up_sites',
                'num_of_participants', 'num_of_barangays', 'total_volume'
            ]));
        } elseif ($reportTypeLC == 'monthly') {
            $filteredData = array_merge($filteredData, $request->only(['month']));
        } elseif ($reportTypeLC == 'quarterly') {
            $filteredData = array_merge($filteredData, $request->only(['quarter_number']));
        } elseif ($reportTypeLC == 'semestral') {
            $filteredData = array_merge($filteredData, $request->only(['sem_number']));
        } elseif ($reportTypeLC == 'annual') {
            // No additional fields needed for annual reports
        }

        // Create a new request with only the filtered data
        $filteredRequest = new Request($filteredData);
        if ($request->hasFile('file')) {
            $filteredRequest->files->add(['file' => $request->file('file')]);
        }

        // Validate the filtered request
        $validator = Validator::make($filteredRequest->all(), $validationRules);

        if ($validator->fails()) {
            // Check if the request is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Get the report type object for validation
            $reportTypeObj = ReportType::findOrFail($filteredRequest->report_type_id);
            Log::info('Report type object found', ['id' => $reportTypeObj->id, 'name' => $reportTypeObj->name]);

            // Log if the report deadline has passed, but still allow updates
            if ($reportTypeObj->deadline < now()) {
                Log::warning('Report deadline has passed, but allowing update', ['deadline' => $reportTypeObj->deadline, 'now' => now()]);
                // We're removing the return statement to allow updates even after deadline
            }

            // We already found the report at the beginning of the method
            // No need to find it again
            Log::info('Using previously found report', ['id' => $report->id, 'type' => $reportType]);

            // Allow resubmission for any status except approved
            if ($report->status === 'approved') {
                Log::warning('Attempt to update approved report', ['report_id' => $report->id, 'status' => $report->status]);
                return back()->with('error', 'Approved reports cannot be updated');
            }

            // Get the report type model for reference
            $reportTypeModel = ReportType::find($report->report_type_id);
            if (!$reportTypeModel) {
                Log::warning('Report type model not found', [
                    'report_type_id' => $report->report_type_id
                ]);
                // Continue anyway since we already have the report
            } elseif ($reportTypeModel->deadline < now()) {
                Log::warning('Report deadline has passed (second check), but allowing update', [
                    'report_type_id' => $report->report_type_id,
                    'deadline' => $reportTypeModel->deadline,
                    'now' => now()
                ]);
                // We're removing the return statement to allow updates even after deadline
            }

            // Set status for resubmitted report
            $newStatus = 'resubmitted';

            // Handle file upload
            $filePath = null;
            $fileName = null;

            if ($filteredRequest->hasFile('file')) {
                try {
                    $file = $filteredRequest->file('file');

                    // Log file information
                    Log::info('Processing file upload', [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ]);

                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('reports', $fileName, 'public');

                    Log::info('File stored successfully', [
                        'path' => $filePath,
                        'filename' => $fileName
                    ]);
                } catch (\Exception $e) {
                    Log::error('File upload error: ' . $e->getMessage(), [
                        'file' => $filteredRequest->file('file')->getClientOriginalName()
                    ]);
                    throw new \Exception('Error uploading file: ' . $e->getMessage());
                }
            } else {
                // If no new file is provided, use the existing file
                Log::info('No new file provided, using existing file');
                $filePath = $report->file_path;
                $fileName = $report->file_name;
            }

            // Update the existing report instead of creating a new one
            $updateData = [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'status' => $newStatus,
                'deadline' => $report->deadline
            ];
            if ($reportType === 'weekly') {
                $updateData = array_merge($updateData, [
                    'month' => $filteredRequest->month,
                    'week_number' => $filteredRequest->week_number,
                    'num_of_clean_up_sites' => $filteredRequest->num_of_clean_up_sites,
                    'num_of_participants' => $filteredRequest->num_of_participants,
                    'num_of_barangays' => $filteredRequest->num_of_barangays,
                    'total_volume' => $filteredRequest->total_volume
                ]);
            } elseif ($reportType === 'monthly') {
                $updateData = array_merge($updateData, [
                    'month' => $filteredRequest->month
                ]);
            } elseif ($reportType === 'quarterly') {
                $updateData = array_merge($updateData, [
                    'quarter_number' => $filteredRequest->quarter_number
                ]);
            } elseif ($reportType === 'semestral') {
                $updateData = array_merge($updateData, [
                    'sem_number' => $filteredRequest->sem_number
                ]);
            }
            $report->update($updateData);
            Log::info('Report updated for resubmission', ['report_id' => $report->id, 'type' => $reportType]);
            DB::commit();
            Log::info('Database transaction committed successfully');
            $successMessage = $report->status === 'rejected'
                ? 'Report resubmitted successfully. It will be reviewed by the admin.'
                : 'Report updated successfully. Your new submission has been saved.';
            session()->flash('success', $successMessage);
            session()->flash('showSuccessModal', true);
            session()->flash('reportStatus', $report->status);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirect' => route('barangay.submissions')
                ]);
            }
            return redirect()->route('barangay.submissions');
        } catch (\Exception $e) {
            DB::rollBack();

            // Get detailed error information
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'report_id' => $reportId,
                'report_type' => $reportType ?? 'unknown',
                'user_id' => Auth::id()
            ];

            Log::error('Report resubmission error', $errorDetails);

            // Create a user-friendly error message
            $errorMessage = $report && $report->status === 'rejected'
                ? 'Failed to resubmit report. Please try again.'
                : 'Failed to update report. Please try again.';

            // Add technical details for development environment
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }

            // Check if the request is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'debug' => config('app.debug') ? $errorDetails : null
                ], 500);
            }

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Direct file download method that ensures the current user can only access their own files.
     * This is a simplified version that directly checks the user ID without complex logic.
     */
    public function directDownloadFile($id)
    {
        try {
            $userId = Auth::id();
            Log::info('Direct file download request', [
                'id' => $id,
                'user_id' => $userId
            ]);

            // Parse the unique identifier to get the table name and ID
            $parts = explode('_', $id);

            if (count($parts) < 2) {
                // If the ID doesn't contain an underscore, it's an old-style ID
                $reportId = $id;
                $reportTable = null;
                Log::info('Old-style ID detected', ['id' => $id]);
            } else {
                // Extract the table name and ID from the unique identifier
                $reportTable = $parts[0];
                $reportId = $parts[1];
                Log::info('Parsed report ID', ['table' => $reportTable, 'id' => $reportId]);
            }

            // Find the report based on the table name and ID
            $report = null;

            if ($reportTable) {
                // If we know the table, directly query that table
                switch ($reportTable) {
                    case 'weekly':
                        $report = WeeklyReport::where('id', $reportId)
                            ->where('user_id', $userId)
                            ->first();
                        break;
                    case 'monthly':
                        $report = MonthlyReport::where('id', $reportId)
                            ->where('user_id', $userId)
                            ->first();
                        break;
                    case 'quarterly':
                        $report = QuarterlyReport::where('id', $reportId)
                            ->where('user_id', $userId)
                            ->first();
                        break;
                    case 'semestral':
                        $report = SemestralReport::where('id', $reportId)
                            ->where('user_id', $userId)
                            ->first();
                        break;
                    case 'annual':
                        $report = AnnualReport::where('id', $reportId)
                            ->where('user_id', $userId)
                            ->first();
                        break;
                }
            } else {
                // If we don't know the table, check all tables
                $report = WeeklyReport::where('id', $reportId)
                    ->where('user_id', $userId)
                    ->first();

                if (!$report) {
                    $report = MonthlyReport::where('id', $reportId)
                        ->where('user_id', $userId)
                        ->first();
                }

                if (!$report) {
                    $report = QuarterlyReport::where('id', $reportId)
                        ->where('user_id', $userId)
                        ->first();
                }

                if (!$report) {
                    $report = SemestralReport::where('id', $reportId)
                        ->where('user_id', $userId)
                        ->first();
                }

                if (!$report) {
                    $report = AnnualReport::where('id', $reportId)
                        ->where('user_id', $userId)
                        ->first();
                }
            }

            if (!$report) {
                Log::error('Report not found or access denied', [
                    'id' => $id,
                    'user_id' => $userId
                ]);
                return response()->json(['error' => 'Report not found or you do not have permission to access it.'], 404);
            }

            // Check if the file exists
            if (!Storage::disk('public')->exists($report->file_path)) {
                Log::error('File not found in storage', [
                    'file_path' => $report->file_path
                ]);
                return response()->json(['error' => 'File not found in storage.'], 404);
            }

            // Get the full path to the file
            $path = Storage::disk('public')->path($report->file_path);

            // Get the file's mime type
            $mimeType = mime_content_type($path);
            $fileName = basename($report->file_path);

            Log::info('File details', [
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
            }

            if (in_array($mimeType, $viewableTypes)) {
                return response()->file($path, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    'Cache-Control' => 'public, max-age=0',
                    'Accept-Ranges' => 'bytes'
                ]);
            }

            // For non-viewable types, force download
            return response()->download($path, $fileName);
        } catch (\Exception $e) {
            Log::error('Error in directDownloadFile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error accessing file: ' . $e->getMessage()], 500);
        }
    }
}
