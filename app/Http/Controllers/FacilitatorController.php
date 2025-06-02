<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Models\MonthlyReport;
use App\Models\QuarterlyReport;
use App\Models\AnnualReport;
use App\Models\ReportType;
use App\Models\Cluster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacilitatorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // The middleware is already applied in the routes file
        // This is just for additional security
    }

    /**
     * Show the facilitator dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard()
    {
        $facilitator = Auth::user();

        // Get clusters assigned to the facilitator
        // Check if the assignedClusters relationship exists and is callable
        if (method_exists($facilitator, 'assignedClusters') && is_callable([$facilitator, 'assignedClusters'])) {
            $clusterIds = $facilitator->assignedClusters()->pluck('clusters.id')->toArray();
        } else {
            // Fallback: get all clusters if the relationship doesn't exist
            $clusterIds = DB::table('clusters')->where('is_active', true)->pluck('id')->toArray();
        }

        // Get barangays in those clusters
        $barangays = User::where('user_type', 'barangay')
            ->whereIn('cluster_id', $clusterIds)
            ->where('is_active', true)
            ->get();

        // Get recent report submissions from barangays in facilitator's clusters
        $recentReports = DB::table('weekly_reports')
            ->join('users', 'weekly_reports.user_id', '=', 'users.id')
            ->join('report_types', 'weekly_reports.report_type_id', '=', 'report_types.id')
            ->whereIn('users.cluster_id', $clusterIds)
            ->where('users.is_active', true)
            ->select('weekly_reports.*', 'users.name as barangay_name', 'report_types.name as report_name')
            ->orderBy('weekly_reports.created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($report) {
                $report->type = 'weekly';
                return $report;
            });

        $recentReports = $recentReports->merge(
            DB::table('monthly_reports')
                ->join('users', 'monthly_reports.user_id', '=', 'users.id')
                ->join('report_types', 'monthly_reports.report_type_id', '=', 'report_types.id')
                ->whereIn('users.cluster_id', $clusterIds)
                ->where('users.is_active', true)
                ->select('monthly_reports.*', 'users.name as barangay_name', 'report_types.name as report_name')
                ->orderBy('monthly_reports.created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($report) {
                    $report->type = 'monthly';
                    return $report;
                })
        );

        $recentReports = $recentReports->merge(
            DB::table('quarterly_reports')
                ->join('users', 'quarterly_reports.user_id', '=', 'users.id')
                ->join('report_types', 'quarterly_reports.report_type_id', '=', 'report_types.id')
                ->whereIn('users.cluster_id', $clusterIds)
                ->where('users.is_active', true)
                ->select('quarterly_reports.*', 'users.name as barangay_name', 'report_types.name as report_name')
                ->orderBy('quarterly_reports.created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($report) {
                    $report->type = 'quarterly';
                    return $report;
                })
        );

        $recentReports = $recentReports->merge(
            DB::table('annual_reports')
                ->join('users', 'annual_reports.user_id', '=', 'users.id')
                ->join('report_types', 'annual_reports.report_type_id', '=', 'report_types.id')
                ->whereIn('users.cluster_id', $clusterIds)
                ->where('users.is_active', true)
                ->select('annual_reports.*', 'users.name as barangay_name', 'report_types.name as report_name')
                ->orderBy('annual_reports.created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($report) {
                    $report->type = 'annual';
                    return $report;
                })
        );

        $recentReports = $recentReports->sortByDesc('created_at')->take(5);

        // Get upcoming deadlines
        $reportTypes = ReportType::all();
        $upcomingDeadlines = [];

        foreach ($reportTypes as $reportType) {
            $deadline = $this->calculateNextDeadline($reportType);
            if ($deadline) {
                $upcomingDeadlines[] = [
                    'report_type' => $reportType->name,
                    'frequency' => $reportType->frequency,
                    'deadline' => $deadline,
                    'days_remaining' => Carbon::now()->diffInDays($deadline, false)
                ];
            }
        }

        // Sort by days remaining (ascending)
        usort($upcomingDeadlines, function($a, $b) {
            return $a['days_remaining'] <=> $b['days_remaining'];
        });

        // Take only upcoming deadlines (positive days remaining)
        $upcomingDeadlines = array_filter($upcomingDeadlines, function($deadline) {
            return $deadline['days_remaining'] >= 0;
        });

        // Take only the next 5 deadlines
        $upcomingDeadlines = array_slice($upcomingDeadlines, 0, 5);

        return view('facilitator.dashboard', compact('barangays', 'recentReports', 'upcomingDeadlines'));
    }

    /**
     * Calculate the next deadline for a report type.
     *
     * @param \App\Models\ReportType $reportType
     * @return \Carbon\Carbon|null
     */
    private function calculateNextDeadline($reportType)
    {
        $now = Carbon::now();
        $deadline = null;

        switch ($reportType->frequency) {
            case 'weekly':
                // Assuming weekly reports are due every Friday
                $deadline = $now->copy()->next(Carbon::FRIDAY);
                break;

            case 'monthly':
                // Assuming monthly reports are due on the 5th of next month
                $deadline = $now->copy()->addMonth()->startOfMonth()->addDays(4);
                break;

            case 'quarterly':
                // Assuming quarterly reports are due on the 15th of the month after the quarter ends
                $currentQuarter = ceil($now->month / 3);
                $quarterEndMonth = $currentQuarter * 3;
                $deadline = $now->copy()->month($quarterEndMonth)->endOfMonth()->addDays(15);
                if ($deadline->isPast()) {
                    $deadline = $now->copy()->month($quarterEndMonth + 3)->endOfMonth()->addDays(15);
                }
                break;

            case 'annual':
                // Assuming annual reports are due on January 15th of the next year
                $deadline = $now->copy()->addYear()->startOfYear()->addDays(14);
                if ($now->month == 12 && $now->day > 15) {
                    $deadline = $now->copy()->addYear()->startOfYear()->addDays(14);
                } else if ($now->month == 1 && $now->day < 15) {
                    $deadline = $now->copy()->startOfYear()->addDays(14);
                } else {
                    $deadline = $now->copy()->addYear()->startOfYear()->addDays(14);
                }
                break;
        }

        return $deadline;
    }

    /**
     * Show the view submissions page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function viewSubmissions(Request $request)
    {
        $facilitator = Auth::user();

        // Get clusters assigned to the facilitator
        // Check if the assignedClusters relationship exists and is callable
        if (method_exists($facilitator, 'assignedClusters') && is_callable([$facilitator, 'assignedClusters'])) {
            $clusterIds = $facilitator->assignedClusters()->pluck('clusters.id')->toArray();
        } else {
            // Fallback: get all clusters if the relationship doesn't exist
            $clusterIds = DB::table('clusters')->where('is_active', true)->pluck('id')->toArray();
        }

        // Get barangays in those clusters
        $barangayQuery = User::where('user_type', 'barangay')
            ->whereIn('cluster_id', $clusterIds)
            ->where('is_active', true);

        // Get barangay IDs for report filtering
        $barangayIds = $barangayQuery->pluck('id')->toArray();

        // Get all barangays for the filter dropdown
        $barangays = $barangayQuery->get();

        // Get report types
        $reportTypes = ReportType::all();

        // Base queries for each report type
        $weeklyQuery = WeeklyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds);

        $monthlyQuery = MonthlyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds);

        $quarterlyQuery = QuarterlyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds);

        $annualQuery = AnnualReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds);

        // Apply filters

        // Initialize selectedBarangay as null
        $selectedBarangay = null;

        // Filter by barangay
        if ($request->has('barangay_id') && !empty($request->barangay_id)) {
            $weeklyQuery->where('user_id', $request->barangay_id);
            $monthlyQuery->where('user_id', $request->barangay_id);
            $quarterlyQuery->where('user_id', $request->barangay_id);
            $annualQuery->where('user_id', $request->barangay_id);

            // Get selected barangay for display
            $selectedBarangay = User::find($request->barangay_id);
        }

        // Filter by report type
        if ($request->has('type') && !empty($request->type)) {
            // We'll filter after getting the results
            $filterType = $request->type;
        } else {
            $filterType = null;
        }

        // Filter by status (remarks)
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'reviewed') {
                $weeklyQuery->whereNotNull('remarks');
                $monthlyQuery->whereNotNull('remarks');
                $quarterlyQuery->whereNotNull('remarks');
                $annualQuery->whereNotNull('remarks');
            } elseif ($request->status === 'pending') {
                $weeklyQuery->whereNull('remarks');
                $monthlyQuery->whereNotNull('remarks');
                $quarterlyQuery->whereNull('remarks');
                $annualQuery->whereNull('remarks');
            }
        }

        // Get reports
        $weeklyReports = $weeklyQuery->orderBy('created_at', 'desc')->get()
            ->map(function ($report) {
                $report->type = 'weekly';
                $report->files = $this->getReportFiles($report);
                return $report;
            });

        $monthlyReports = $monthlyQuery->orderBy('created_at', 'desc')->get()
            ->map(function ($report) {
                $report->type = 'monthly';
                $report->files = $this->getReportFiles($report);
                return $report;
            });

        $quarterlyReports = $quarterlyQuery->orderBy('created_at', 'desc')->get()
            ->map(function ($report) {
                $report->type = 'quarterly';
                $report->files = $this->getReportFiles($report);
                return $report;
            });

        $annualReports = $annualQuery->orderBy('created_at', 'desc')->get()
            ->map(function ($report) {
                $report->type = 'annual';
                $report->files = $this->getReportFiles($report);
                return $report;
            });

        // Combine all reports
        $allReports = $weeklyReports->concat($monthlyReports)
            ->concat($quarterlyReports)
            ->concat($annualReports);

        // Apply type filter if set
        if ($filterType) {
            $allReports = $allReports->filter(function ($report) use ($filterType) {
                return $report->type === $filterType;
            });
        }

        // Apply search filter if set
        if ($request->has('search') && !empty($request->search)) {
            $search = strtolower($request->search);
            $allReports = $allReports->filter(function ($report) use ($search) {
                return stripos($report->user->name, $search) !== false ||
                       stripos($report->reportType->name, $search) !== false ||
                       stripos($report->title, $search) !== false;
            });
        }

        // Sort by created_at
        $reports = $allReports->sortByDesc('created_at');

        // Check if this is an AJAX request
        if ($request->ajax()) {
            return view('facilitator.partials.reports-table', compact('reports'))->render();
        }

        return view('facilitator.view-submissions', compact('reports', 'reportTypes', 'barangays', 'selectedBarangay'));
    }

    /**
     * Get files for a report.
     *
     * @param mixed $report
     * @return array
     */
    private function getReportFiles($report)
    {
        if (!$report->file_path) {
            return [];
        }

        return [
            [
                'id' => $report->id,
                'original_name' => $report->file_name ?? basename($report->file_path),
                'file_path' => $report->file_path
            ]
        ];
    }

    /**
     * Add remarks to a report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addRemarks(Request $request, $id)
    {
        // Get clusters assigned to the facilitator
        $facilitator = Auth::user();

        // Check if the assignedClusters relationship exists and is callable
        if (method_exists($facilitator, 'assignedClusters') && is_callable([$facilitator, 'assignedClusters'])) {
            $clusterIds = $facilitator->assignedClusters()->pluck('clusters.id')->toArray();
        } else {
            // Fallback: get all clusters if the relationship doesn't exist
            $clusterIds = DB::table('clusters')->where('is_active', true)->pluck('id')->toArray();
        }

        // Get barangays in those clusters
        $barangayIds = User::where('user_type', 'barangay')
            ->whereIn('cluster_id', $clusterIds)
            ->where('is_active', true)
            ->pluck('id');

        $request->validate([
            'remarks' => 'required|string',
            'type' => 'required|in:weekly,monthly,quarterly,annual',
        ]);

        $reportClass = null;
        switch ($request->type) {
            case 'weekly':
                $reportClass = WeeklyReport::class;
                break;
            case 'monthly':
                $reportClass = MonthlyReport::class;
                break;
            case 'quarterly':
                $reportClass = QuarterlyReport::class;
                break;
            case 'annual':
                $reportClass = AnnualReport::class;
                break;
        }

        $report = $reportClass::with('user')->findOrFail($id);

        // Verify the report belongs to a barangay in the facilitator's clusters
        if (!$barangayIds->contains($report->user_id)) {
            return back()->with('error', 'You can only add remarks to reports from barangays in your assigned clusters.');
        }

        // Update the remarks
        $report->update([
            'remarks' => $request->remarks
        ]);

        return back()->with('success', 'Remarks added successfully.');
    }

    /**
     * Approve a report (facilitator action).
     */
    public function approve(Request $request, $id)
    {
        $facilitator = Auth::user();
        if (method_exists($facilitator, 'assignedClusters') && is_callable([$facilitator, 'assignedClusters'])) {
            $clusterIds = $facilitator->assignedClusters()->pluck('clusters.id')->toArray();
        } else {
            $clusterIds = DB::table('clusters')->where('is_active', true)->pluck('id')->toArray();
        }
        $barangayIds = User::where('user_type', 'barangay')
            ->whereIn('cluster_id', $clusterIds)
            ->where('is_active', true)
            ->pluck('id');

        // Try to find the report in all report types
        $report = null;
        foreach ([WeeklyReport::class, MonthlyReport::class, QuarterlyReport::class, AnnualReport::class] as $model) {
            $candidate = $model::find($id);
            if ($candidate && $barangayIds->contains($candidate->user_id)) {
                $report = $candidate;
                break;
            }
        }
        if (!$report) {
            return back()->with('error', 'Report not found or not in your assigned clusters.');
        }
        $report->status = 'approved';
        $report->save();
        return back()->with('success', 'Report approved successfully.');
    }

    /**
     * Resubmit a report (facilitator action).
     */
    public function resubmit(Request $request, $id)
    {
        $facilitator = Auth::user();
        if (method_exists($facilitator, 'assignedClusters') && is_callable([$facilitator, 'assignedClusters'])) {
            $clusterIds = $facilitator->assignedClusters()->pluck('clusters.id')->toArray();
        } else {
            $clusterIds = DB::table('clusters')->where('is_active', true)->pluck('id')->toArray();
        }
        $barangayIds = User::where('user_type', 'barangay')
            ->whereIn('cluster_id', $clusterIds)
            ->where('is_active', true)
            ->pluck('id');

        // Try to find the report in all report types
        $report = null;
        foreach ([WeeklyReport::class, MonthlyReport::class, QuarterlyReport::class, AnnualReport::class] as $model) {
            $candidate = $model::find($id);
            if ($candidate && $barangayIds->contains($candidate->user_id)) {
                $report = $candidate;
                break;
            }
        }
        if (!$report) {
            return back()->with('error', 'Report not found or not in your assigned clusters.');
        }
        $report->status = 'resubmit';
        $report->save();
        return back()->with('success', 'Report marked for resubmission.');
    }
}
