<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportFile;
use App\Models\ReportType;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ReportSubmissionRequest;

class OptimizedBarangayController extends Controller
{
    protected $reportService;
    
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
        
        // Authentication check in the constructor
        if (!Auth::check() || Auth::user()->role !== 'barangay') {
            abort(403, 'Unauthorized access.');
        }
    }
    
    /**
     * Display the barangay dashboard with optimized queries and caching.
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();
            $cacheKey = "barangay_dashboard_{$user->id}";
            $cacheDuration = 60; // Cache for 60 minutes
            
            // Try to get the data from cache first
            if (Cache::has($cacheKey)) {
                return view('barangay.dashboard', Cache::get($cacheKey));
            }
            
            // If not cached, generate the data
            $reports = $this->reportService->getReportsForUser($user);
            
            // Calculate statistics
            $totalReports = $reports->count();
            $submittedReports = $reports->where('status', Report::STATUS_SUBMITTED)->count();
            $noSubmissionReports = $reports->where('status', Report::STATUS_NO_SUBMISSION)->count();
            
            // Get recent reports (last 5)
            $recentReports = $reports->sortByDesc('created_at')->take(5);
            
            // Get submitted report type IDs
            $submittedReportTypeIds = $reports->pluck('report_type_id')->unique();
            
            // Get upcoming deadlines with eager loading
            $upcomingDeadlines = ReportType::where('deadline', '>=', now())
                ->whereNotIn('id', $submittedReportTypeIds)
                ->orderBy('deadline')
                ->take(10)
                ->get()
                ->map(function ($reportType) {
                    $reportType->deadline = Carbon::parse($reportType->deadline);
                    return $reportType;
                });
            
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
            
            $allReportTypes = ReportType::all();
            
            // Prepare data to store in cache
            $viewData = compact(
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
            );
            
            // Store in cache for future requests
            Cache::put($cacheKey, $viewData, $cacheDuration);
            
            return view('barangay.dashboard', $viewData);
        } catch (\Exception $e) {
            Log::error('Error in barangay dashboard: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while loading the dashboard: ' . $e->getMessage());
        }
    }
    
    /**
     * Display all reports for the current user with pagination.
     */
    public function viewReports(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            // Build filters from the request
            $filters = [
                'frequency' => $request->get('frequency'),
                'status' => $request->get('status'),
                'report_type_id' => $request->get('report_type_id'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_dir' => $request->get('sort_dir', 'desc'),
            ];
            
            // Use the service to get filtered reports
            $allReports = $this->reportService->getReportsForUser($user, $filters);
            
            // Manually paginate the results
            $reports = new \Illuminate\Pagination\LengthAwarePaginator(
                $allReports->forPage($page, $perPage),
                $allReports->count(),
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query()
                ]
            );
            
            // Get all report types for filtering
            $reportTypes = ReportType::orderBy('name')->get();
            
            return view('barangay.view-reports', compact('reports', 'reportTypes'));
        } catch (\Exception $e) {
            Log::error('Error in view reports: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while loading reports: ' . $e->getMessage());
        }
    }
    
    /**
     * Show overdue reports for the current barangay user.
     */
    public function overdueReports()
    {
        try {
            $user = Auth::user();
            $overdueReports = $this->reportService->getOverdueReportsForUser($user);
            
            return view('barangay.overdue-reports', compact('overdueReports'));
        } catch (\Exception $e) {
            Log::error('Error in overdue reports: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while fetching overdue reports: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for creating a new report submission.
     */
    public function submitReport()
    {
        $user = Auth::user();
        
        // Get all submitted report IDs for this user
        $submittedReportIds = Report::where('user_id', $user->id)
            ->pluck('report_type_id')
            ->unique()
            ->toArray();
        
        // Get available report types (not already submitted)
        $reportTypes = ReportType::whereNotIn('id', $submittedReportIds)
            ->orderBy('name')
            ->get();
        
        // Group by frequency for easy filtering
        $reportTypesByFrequency = [
            'weekly' => $reportTypes->where('frequency', 'weekly'),
            'monthly' => $reportTypes->where('frequency', 'monthly'),
            'quarterly' => $reportTypes->where('frequency', 'quarterly'),
            'semestral' => $reportTypes->where('frequency', 'semestral'),
            'annual' => $reportTypes->where('frequency', 'annual'),
        ];
        
        return view('barangay.submit-report', compact('reportTypesByFrequency'));
    }
    
    /**
     * Store a newly created report in storage.
     */
    public function store(ReportSubmissionRequest $request)
    {
        try {
            $user = Auth::user();
            
            // Get validated data
            $validatedData = $request->validated();
            
            // Get the report type
            $reportType = ReportType::findOrFail($validatedData['report_type_id']);
            
            // Create the report using the service
            $report = $this->reportService->createReport(
                $user, 
                $validatedData, 
                $request->file('file')
            );
            
            return redirect()->route('barangay.submissions')
                ->with('success', 'Report submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Error submitting report: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to submit report: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Resubmit a report with updated information and/or files.
     */
    public function resubmit(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            // Find the report
            $report = Report::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            
            // Validate the request
            $validator = Validator::make($request->all(), [
                'remarks' => 'nullable|string',
                'file' => 'nullable|file|max:10240', // 10MB max
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Create data array for update
            $data = [
                'remarks' => $request->remarks,
            ];
            
            // Add frequency-specific fields based on the report's frequency
            switch ($report->frequency) {
                case 'weekly':
                    $this->validateFrequencyFields($request, [
                        'num_of_clean_up_sites' => 'nullable|integer|min:0',
                        'num_of_participants' => 'nullable|integer|min:0',
                        'num_of_barangays' => 'nullable|integer|min:0',
                        'total_volume' => 'nullable|numeric|min:0',
                    ]);
                    
                    $data['num_of_clean_up_sites'] = $request->num_of_clean_up_sites;
                    $data['num_of_participants'] = $request->num_of_participants;
                    $data['num_of_barangays'] = $request->num_of_barangays;
                    $data['total_volume'] = $request->total_volume;
                    break;
            }
            
            // Update the report using the service
            $this->reportService->updateReport($report, $data, $request->file('file'));
            
            return redirect()->route('barangay.submissions')
                ->with('success', 'Report updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error resubmitting report: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'report_id' => $id,
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update report: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Display all submissions for the current user.
     */
    public function submissions(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $frequency = $request->get('frequency');
            
            // Build filters
            $filters = [
                'frequency' => $frequency,
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_dir' => $request->get('sort_dir', 'desc'),
            ];
            
            // Use the service to get filtered reports
            $allReports = $this->reportService->getReportsForUser($user, $filters);
            
            // Manually paginate the results
            $submissions = new \Illuminate\Pagination\LengthAwarePaginator(
                $allReports->forPage($page, $perPage),
                $allReports->count(),
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query()
                ]
            );
            
            return view('barangay.submissions', compact('submissions', 'frequency'));
        } catch (\Exception $e) {
            Log::error('Error fetching submissions: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while loading submissions: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate frequency-specific fields.
     */
    protected function validateFrequencyFields(Request $request, array $rules)
    {
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->throwResponse();
        }
    }
    
    /**
     * Download a file directly.
     */
    public function directDownloadFile($id)
    {
        try {
            $file = ReportFile::findOrFail($id);
            
            // Check if the file belongs to the current user
            if ($file->user_id !== Auth::id()) {
                abort(403, 'You do not have permission to access this file.');
            }
            
            // Check if the file exists in storage
            if (!$file->fileExists()) {
                return redirect()->back()->with('error', 'File not found in storage.');
            }
            
            // Return the file as a download
            return Storage::download(
                $file->full_path,
                $file->original_filename
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to download file: ' . $e->getMessage());
        }
    }
} 