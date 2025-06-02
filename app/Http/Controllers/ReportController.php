<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ReportType, WeeklyReport, MonthlyReport, QuarterlyReport, SemestralReport, AnnualReport};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\ReportRemarksNotification;
use App\Models\User;

class ReportController extends Controller
{
    /**
     * Show the report submission form.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            // Initialize queries with relationships
            $weeklyQuery = WeeklyReport::with(['user', 'reportType']);
            $monthlyQuery = MonthlyReport::with(['user', 'reportType']);
            $quarterlyQuery = QuarterlyReport::with(['user', 'reportType']);
            $semestralQuery = SemestralReport::with(['user', 'reportType']);
            $annualQuery = AnnualReport::with(['user', 'reportType']);

            // Filter by barangay (user) if specified
            if ($request->filled('barangay_id')) {
                $barangayId = $request->barangay_id;
                $weeklyQuery->where('user_id', $barangayId);
                $monthlyQuery->where('user_id', $barangayId);
                $quarterlyQuery->where('user_id', $barangayId);
                $semestralQuery->where('user_id', $barangayId);
                $annualQuery->where('user_id', $barangayId);
            }

            // Apply type filter if specified
            if ($request->filled('type')) {
                $type = $request->type;
                // Only get reports of the specified type
                if ($type == 'weekly') {
                    $monthlyQuery->whereRaw('1=0'); // Force empty result
                    $quarterlyQuery->whereRaw('1=0');
                    $semestralQuery->whereRaw('1=0');
                    $annualQuery->whereRaw('1=0');
                } elseif ($type == 'monthly') {
                    $weeklyQuery->whereRaw('1=0');
                    $quarterlyQuery->whereRaw('1=0');
                    $semestralQuery->whereRaw('1=0');
                    $annualQuery->whereRaw('1=0');
                } elseif ($type == 'quarterly') {
                    $weeklyQuery->whereRaw('1=0');
                    $monthlyQuery->whereRaw('1=0');
                    $semestralQuery->whereRaw('1=0');
                    $annualQuery->whereRaw('1=0');
                } elseif ($type == 'semestral') {
                    $weeklyQuery->whereRaw('1=0');
                    $monthlyQuery->whereRaw('1=0');
                    $quarterlyQuery->whereRaw('1=0');
                    $annualQuery->whereRaw('1=0');
                } elseif ($type == 'annual') {
                    $weeklyQuery->whereRaw('1=0');
                    $monthlyQuery->whereRaw('1=0');
                    $quarterlyQuery->whereRaw('1=0');
                    $semestralQuery->whereRaw('1=0');
                }
            }

            // Apply status filter if specified
            if ($request->filled('status')) {
                $status = $request->status;
                $weeklyQuery->where('status', $status);
                $monthlyQuery->where('status', $status);
                $quarterlyQuery->where('status', $status);
                $semestralQuery->where('status', $status);
                $annualQuery->where('status', $status);
            }

            // Apply search filter if specified
            if ($request->filled('search')) {
                $search = $request->search;
                $weeklyQuery->whereHas('reportType', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });

                $monthlyQuery->whereHas('reportType', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });

                $quarterlyQuery->whereHas('reportType', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });

                $semestralQuery->whereHas('reportType', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });

                $annualQuery->whereHas('reportType', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Get all reports with their relationships and add unique identifiers
            $weeklyReports = $weeklyQuery->get()->map(function ($report) {
                $report->model_type = 'WeeklyReport';
                $report->unique_id = 'weekly_' . $report->id;
                return $report;
            });

            $monthlyReports = $monthlyQuery->get()->map(function ($report) {
                $report->model_type = 'MonthlyReport';
                $report->unique_id = 'monthly_' . $report->id;
                return $report;
            });

            $quarterlyReports = $quarterlyQuery->get()->map(function ($report) {
                $report->model_type = 'QuarterlyReport';
                $report->unique_id = 'quarterly_' . $report->id;
                return $report;
            });

            $semestralReports = $semestralQuery->get()->map(function ($report) {
                $report->model_type = 'SemestralReport';
                $report->unique_id = 'semestral_' . $report->id;
                return $report;
            });

            $annualReports = $annualQuery->get()->map(function ($report) {
                $report->model_type = 'AnnualReport';
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

            // Group reports by user_id and report_type_id and get only the latest submission for each combination
            $latestReports = collect();
            $groupedReports = $allReports->groupBy(function($report) {
                return $report->user_id . '_' . $report->report_type_id;
            });

            foreach ($groupedReports as $group) {
                // Sort by created_at in descending order and take the first one (latest)
                $latestReport = $group->sortByDesc('created_at')->first();
                if ($latestReport) {
                    $latestReports->push($latestReport);
                }
            }

            // Sort the filtered collection by created_at in descending order
            $reports = $latestReports->sortByDesc('created_at');

            // Apply timeliness filter if specified
            if ($request->filled('timeliness')) {
                $timeliness = $request->timeliness;
                $reports = $reports->filter(function($report) use ($timeliness) {
                    $isLate = Carbon::parse($report->created_at)->isAfter($report->reportType->deadline);
                    return ($timeliness === 'late') ? $isLate : !$isLate;
                });
            }

            // Create a paginator
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

            // Get all barangay users for the filter dropdown
            $barangays = \App\Models\User::where('role', 'barangay')
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();

            // Get the selected barangay if any
            $selectedBarangay = null;
            if ($request->filled('barangay_id')) {
                $selectedBarangay = \App\Models\User::find($request->barangay_id);
            }

            return view('admin.view-submissions', compact('reports', 'barangays', 'selectedBarangay'));
        } catch (\Exception $e) {
            Log::error('Error in admin view submissions: ' . $e->getMessage());
            return view('admin.view-submissions', [
                'reports' => collect(),
                'barangays' => \App\Models\User::where('role', 'barangay')->where('is_active', true)->get(),
                'selectedBarangay' => null
            ])->with('error', 'An error occurred while loading submissions.');
        }
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
            Log::info('Updating report remarks for ID: ' . $id);

            $validated = $request->validate([
                'remarks' => 'nullable|string|max:1000',
                'type' => 'required|in:weekly,monthly,quarterly,semestral,annual'
            ]);

            // Check if the user is an admin
            $isAdmin = Auth::user()->role === 'admin';
            if (!$isAdmin) {
                Log::error('Non-admin user attempted to update report: ' . Auth::id());
                return redirect()->back()->with('error', 'You do not have permission to update this report.');
            }

            // Use the findReport helper method to locate the report
            $report = $this->findReport($id);

            if (!$report) {
                Log::error('Report not found with ID: ' . $id);
                return redirect()->back()->with('error', 'Report not found. Please try again.');
            }

            Log::info('Found report', [
                'id' => $report->id,
                'type' => get_class($report),
                'remarks' => $validated['remarks']
            ]);

            // Get the old remarks to check if they've changed
            $oldRemarks = $report->remarks;

            // Only update remarks
            $report->update([
                'remarks' => $validated['remarks']
            ]);

            // Send notification if remarks have been added or changed
            if ($validated['remarks'] && $validated['remarks'] !== $oldRemarks) {
                Log::info('Remarks have changed, preparing to send notification', [
                    'old_remarks' => $oldRemarks,
                    'new_remarks' => $validated['remarks'],
                    'report_id' => $report->id,
                    'report_type' => $validated['type']
                ]);

                // Get the barangay user who submitted the report
                $barangayUser = User::find($report->user_id);

                if ($barangayUser) {
                    Log::info('Found barangay user', [
                        'barangay_id' => $barangayUser->id,
                        'barangay_name' => $barangayUser->name,
                        'barangay_email' => $barangayUser->email,
                        'barangay_role' => $barangayUser->role
                    ]);

                    // Get admin name
                    $adminName = Auth::user()->name;
                    Log::info('Admin user', [
                        'admin_id' => Auth::id(),
                        'admin_name' => $adminName
                    ]);

                    try {
                        // Send notification
                        $barangayUser->notify(new ReportRemarksNotification(
                            $report,
                            $validated['remarks'],
                            $validated['type'],
                            $adminName
                        ));

                        Log::info('Email notification sent to barangay user', [
                            'barangay_id' => $barangayUser->id,
                            'barangay_email' => $barangayUser->email,
                            'report_id' => $report->id,
                            'report_type' => $validated['type']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send notification to barangay user', [
                            'barangay_id' => $barangayUser->id,
                            'barangay_email' => $barangayUser->email,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } else {
                    Log::warning('Barangay user not found for report', [
                        'report_id' => $report->id,
                        'user_id' => $report->user_id
                    ]);
                }
            } else {
                Log::info('Remarks have not changed, no notification sent', [
                    'old_remarks' => $oldRemarks,
                    'new_remarks' => $validated['remarks'] ?? null,
                    'report_id' => $report->id
                ]);
            }

            return redirect()->back()->with('success', 'Report remarks updated successfully.')->with('remarks_updated', true);
        } catch (\Exception $e) {
            Log::error('Error updating report: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to update report remarks: ' . $e->getMessage());
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
        // Always check user ID for resubmission to ensure users can only resubmit their own reports
        $report = $this->findReport($id, true);
        if (!$report) {
            return back()->with('error', 'Report not found or you do not have permission to resubmit this report.');
        }

        $reportTypes = ReportType::all();
        return view('barangay.resubmit-report', compact('report', 'reportTypes'));
    }

    /**
     * Handle report resubmission.
     */
    public function resubmit(Request $request, $id)
    {
        // Always check user ID for resubmission to ensure users can only resubmit their own reports
        $report = $this->findReport($id, true);
        if (!$report) {
            return back()->with('error', 'Report not found or you do not have permission to resubmit this report.');
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
                'status' => 'resubmitted',
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
    private function findReport($id, $checkUserId = false)
    {
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

        // Get the current user ID if we need to check permissions
        $userId = $checkUserId ? Auth::id() : null;
        Log::info('User ID for permission check: ' . ($userId ?? 'not checking'));

        $reportTypes = [
            'weekly' => WeeklyReport::class,
            'monthly' => MonthlyReport::class,
            'quarterly' => QuarterlyReport::class,
            'semestral' => SemestralReport::class,
            'annual' => AnnualReport::class
        ];

        if ($reportTable && isset($reportTypes[$reportTable])) {
            // If we know the table, we can directly query that table
            $model = $reportTypes[$reportTable];
            Log::info("Directly checking {$reportTable} reports table for ID: {$reportId}");

            try {
                // Build the query
                $query = $model::where('id', $reportId);

                // Add user ID check if needed
                if ($checkUserId && $userId) {
                    $query->where('user_id', $userId);
                    Log::info("Adding user_id filter: {$userId}");
                }

                $report = $query->first();

                if ($report) {
                    Log::info("Found report in {$reportTable} reports table", [
                        'report_id' => $report->id,
                        'report_type' => get_class($report),
                        'user_id' => $report->user_id
                    ]);
                    return $report;
                } else {
                    Log::warning("No report found in {$reportTable} table with ID {$reportId}" .
                        ($checkUserId ? " for user {$userId}" : ""));
                }
            } catch (\Exception $e) {
                Log::warning("Failed to find report in {$reportTable} table with ID {$reportId}: " . $e->getMessage());
                // Continue to check other tables
            }
        }

        // If we don't know the table or didn't find the report in the specified table,
        // we need to check all tables
        foreach ($reportTypes as $type => $model) {
            if ($reportTable && $type === $reportTable) {
                // Skip if we already checked this table
                continue;
            }

            Log::info("Checking {$type} reports table for ID: {$reportId}");
            try {
                // Build the query
                $query = $model::where('id', $reportId);

                // Add user ID check if needed
                if ($checkUserId && $userId) {
                    $query->where('user_id', $userId);
                }

                $report = $query->first();

                if ($report) {
                    Log::info("Found report in {$type} reports table", [
                        'report_id' => $report->id,
                        'report_type' => get_class($report),
                        'user_id' => $report->user_id
                    ]);
                    return $report;
                }
            } catch (\Exception $e) {
                Log::warning("Error checking {$type} table: " . $e->getMessage());
            }
        }

        Log::warning("Report not found with ID: {$id}" . ($checkUserId ? " for user " . $userId : ""));
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
            Log::info('Attempting to find report with ID: ' . $id);

            // Check if the user is an admin
            $isAdmin = Auth::user()->role === 'admin';
            $userId = Auth::id();
            Log::info('User role: ' . Auth::user()->role . ', User ID: ' . $userId);

            // Parse the unique identifier to get the table name and ID
            $parts = explode('_', $id);

            if (count($parts) < 2) {
                // If the ID doesn't contain an underscore, it's an old-style ID
                $reportId = $id;
                $reportTable = null;
            } else {
                // Extract the table name and ID from the unique identifier
                $reportTable = $parts[0];
                $reportId = $parts[1];
            }

            Log::info('Parsed report ID: ' . $reportId . ', Table: ' . ($reportTable ?? 'unknown'));

            // Find the report based on the user's role
            $report = null;

            if ($isAdmin) {
                // Admins can access any report
                $report = $this->findReport($id, false);
                Log::info('Admin user, finding report without user ID check');
            } else {
                // For barangay users, we need to check the user ID
                Log::info('Barangay user, finding report with user ID check: ' . $userId);

                // Use a direct query approach for better control
                $reportTypes = [
                    'weekly' => WeeklyReport::class,
                    'monthly' => MonthlyReport::class,
                    'quarterly' => QuarterlyReport::class,
                    'semestral' => SemestralReport::class,
                    'annual' => AnnualReport::class
                ];

                if ($reportTable && isset($reportTypes[$reportTable])) {
                    // If we know the table, we can directly query that table
                    $model = $reportTypes[$reportTable];
                    Log::info("Directly checking {$reportTable} reports table for ID: {$reportId} and user_id: {$userId}");

                    $report = $model::where('id', $reportId)
                        ->where('user_id', $userId)
                        ->first();

                    if ($report) {
                        Log::info("Found report in {$reportTable} reports table for user {$userId}");
                    } else {
                        Log::warning("No report found in {$reportTable} table with ID {$reportId} for user {$userId}");
                    }
                } else {
                    // If we don't know the table, we need to check all tables
                    foreach ($reportTypes as $type => $model) {
                        Log::info("Checking {$type} reports table for ID: {$reportId} and user_id: {$userId}");

                        $report = $model::where('id', $reportId)
                            ->where('user_id', $userId)
                            ->first();

                        if ($report) {
                            Log::info("Found report in {$type} reports table for user {$userId}");
                            break;
                        }
                    }
                }
            }

            if (!$report) {
                Log::error('Report not found in any table for ID: ' . $id . ' and user ' . $userId);
                return response()->json(['error' => 'Report not found or you do not have permission to access it.'], 404);
            }

            // Log the file path from the database
            Log::info('File path from database: ' . $report->file_path);

            // Check if the file exists in storage
            if (!Storage::disk('public')->exists($report->file_path)) {
                Log::error('File not found in storage: ' . $report->file_path);
                return response()->json(['error' => 'File not found in storage.'], 404);
            }

            // Get the full path to the file
            $path = Storage::disk('public')->path($report->file_path);
            Log::info('Full file path: ' . $path);

            // Get the file's mime type
            $mimeType = mime_content_type($path);
            $fileName = basename($report->file_path);

            Log::info('File details:', [
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
                Log::info('Using mime type from extension: ' . $mimeType);
            }

            if (in_array($mimeType, $viewableTypes)) {
                Log::info('Serving file as viewable type: ' . $mimeType);
                return response()->file($path, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    'Cache-Control' => 'public, max-age=0',
                    'Accept-Ranges' => 'bytes'
                ]);
            }

            Log::info('File type not viewable, forcing download: ' . $mimeType);
            // For non-viewable types, force download
            return response()->download($path, $fileName);
        } catch (\Exception $e) {
            Log::error('File access error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error accessing file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve a report (admin action).
     */
    public function approve($id)
    {
        $report = $this->findReport($id);
        if (!$report) {
            return back()->with('error', 'Report not found.');
        }
        $report->status = 'approved';
        $report->save();
        return back()->with('success', 'Report approved successfully.');
    }

    /**
     * Reject a report (admin action).
     */
    public function reject($id)
    {
        $report = $this->findReport($id);
        if (!$report) {
            return back()->with('error', 'Report not found.');
        }
        $report->status = 'rejected';
        $report->save();
        return back()->with('success', 'Report rejected successfully.');
    }

    /**
     * Resubmit a report (admin action).
     */
    public function adminResubmit($id)
    {
        $report = $this->findReport($id);
        if (!$report) {
            return back()->with('error', 'Report not found.');
        }
        $report->status = 'resubmit';
        $report->save();
        return back()->with('success', 'Report marked for resubmission.');
    }
}
