<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{WeeklyReport, MonthlyReport, QuarterlyReport, SemestralReport, AnnualReport, ReportType};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $reportTypes = ReportType::orderBy('name')->get();

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

            // Get upcoming deadlines and ensure they are Carbon instances
            // Filter out already submitted report types
            $upcomingDeadlines = ReportType::where('deadline', '>=', now())
                ->whereNotIn('id', $submittedReportTypeIds)
                ->orderBy('deadline')
                ->take(5)
                ->get()
                ->map(function ($reportType) {
                    $reportType->deadline = \Carbon\Carbon::parse($reportType->deadline);
                    return $reportType;
                });

            return view('barangay.dashboard', compact(
                'totalReports',
                'submittedReports',
                'noSubmissionReports',
                'recentReports',
                'upcomingDeadlines',
                'reportTypes'
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
        $userId = Auth::id();
        $perPage = request()->get('per_page', 10);

        // Get all report types that are past their deadline
        $overdueReportTypes = ReportType::where('deadline', '<', now())
            ->get();

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

        // Filter out report types that have already been submitted
        $overdueReports = $overdueReportTypes->filter(function ($reportType) use ($submittedReportTypeIds) {
            return !$submittedReportTypeIds->contains($reportType->id);
        });

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
    }

    public function submissions()
    {
        $userId = Auth::id();
        $perPage = request()->get('per_page', 10);

        // Get all reports for the current user with their relationships
        $weeklyReports = WeeklyReport::with('reportType')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($report) {
                $report->type = 'weekly';
                return $report;
            });

        $monthlyReports = MonthlyReport::with('reportType')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($report) {
                $report->type = 'monthly';
                return $report;
            });

        $quarterlyReports = QuarterlyReport::with('reportType')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($report) {
                $report->type = 'quarterly';
                return $report;
            });

        $semestralReports = SemestralReport::with('reportType')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($report) {
                $report->type = 'semestral';
                return $report;
            });

        $annualReports = AnnualReport::with('reportType')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($report) {
                $report->type = 'annual';
                return $report;
            });

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

        return view('barangay.submissions', compact('reports'));
    }

    public function submitReport()
    {
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

        // Filter out already submitted report types and only show those with valid deadlines
        $reportTypes = ReportType::where('deadline', '>=', now())
            ->whereNotIn('id', $submittedReportTypeIds)
            ->get();

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
            'submittedReportsByFrequency'
        ));
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file'
            ], 500);
        }
    }

    public function downloadFile($id)
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

            return response()->download($path, $report->file_name);
        } catch (\Exception $e) {
            \Log::error('File download error: ' . $e->getMessage());
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
            \Log::error('File view error: ' . $e->getMessage());
            return back()->with('error', 'Failed to view file. Please try again.');
        }
    }

    public function deleteFile($id)
    {
        $report = ReportFile::findOrFail($id);

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

            $validator = \Validator::make($request->all(), $validationRules);

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
                    // Check if user has already submitted this report type
                    $existingReport = WeeklyReport::where('user_id', Auth::id())
                        ->where('report_type_id', $request->report_type_id)
                        ->first();

                    if ($existingReport) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You have already submitted this report type.'
                        ], 422);
                    }

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
                    $existingReport = MonthlyReport::where('user_id', Auth::id())
                        ->where('report_type_id', $request->report_type_id)
                        ->first();

                    if ($existingReport) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You have already submitted this report type.'
                        ], 422);
                    }

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
                    $existingReport = QuarterlyReport::where('user_id', Auth::id())
                        ->where('report_type_id', $request->report_type_id)
                        ->first();

                    if ($existingReport) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You have already submitted this report type.'
                        ], 422);
                    }

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
                    $existingReport = SemestralReport::where('user_id', Auth::id())
                        ->where('report_type_id', $request->report_type_id)
                        ->first();

                    if ($existingReport) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You have already submitted this report type.'
                        ], 422);
                    }

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
                    $existingReport = AnnualReport::where('user_id', Auth::id())
                        ->where('report_type_id', $request->report_type_id)
                        ->first();

                    if ($existingReport) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You have already submitted this report type.'
                        ], 422);
                    }

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
            \Log::error('Report submission error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report. Please try again. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resubmit(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // Try to find the report in each table
            $report = null;
            $reportType = null;

            // Check weekly reports
            $report = WeeklyReport::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
            if ($report) {
                $reportType = 'weekly';
            }

            // Check monthly reports
            if (!$report) {
                $report = MonthlyReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
                if ($report) {
                    $reportType = 'monthly';
                }
            }

            // Check quarterly reports
            if (!$report) {
                $report = QuarterlyReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
                if ($report) {
                    $reportType = 'quarterly';
                }
            }

            // Check semestral reports
            if (!$report) {
                $report = SemestralReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
                if ($report) {
                    $reportType = 'semestral';
                }
            }

            // Check annual reports
            if (!$report) {
                $report = AnnualReport::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();
                if ($report) {
                    $reportType = 'annual';
                }
            }

            if (!$report) {
                return back()->with('error', 'Report not found');
            }

            // Check if the report is rejected
            if ($report->status !== 'rejected') {
                return back()->with('error', 'Only rejected reports can be resubmitted');
            }

            // Check if the report type is still within deadline
            $reportTypeModel = ReportType::find($report->report_type_id);
            if (!$reportTypeModel || $reportTypeModel->deadline < now()) {
                return back()->with('error', 'The deadline for this report has passed');
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('reports', $filename, 'public');

            // Delete old file
            if ($report->file_path) {
                Storage::delete('public/' . $report->file_path);
            }

            $report->update([
                'file_path' => $path,
                'file_name' => $filename,
                'status' => 'pending',
                'remarks' => null,
                'resubmitted_at' => now()
            ]);

            DB::commit();

            return redirect()->route('barangay.submissions')->with('success', 'Report resubmitted successfully. It will be reviewed by the admin.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Report resubmission error: ' . $e->getMessage());
            return back()->with('error', 'Failed to resubmit report. Please try again.');
        }
    }

    /**
     * Change the authenticated user's password (profile modal).
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();
        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $user->password = \Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
}
