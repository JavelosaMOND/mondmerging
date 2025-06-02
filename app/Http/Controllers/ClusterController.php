<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\{WeeklyReport, MonthlyReport, QuarterlyReport, SemestralReport, AnnualReport};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClusterController extends Controller
{
    public function __construct()
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['Facilitator', 'cluster'])) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $clusterId = Auth::id();
        $cluster = User::find($clusterId);

        // Get barangays assigned to this cluster
        $barangays = $cluster->barangays;
        // If this cluster has a parent, also get barangays from the parent
        if ($cluster->parentCluster) {
            $barangays = $barangays->merge($cluster->parentCluster->barangays);
        }

        // Get total number of barangays
        $barangayCount = $barangays->count();

        // Get total reports from assigned barangays
        $barangayIds = $barangays->pluck('id');
        
        // Get report counts by type
        $weeklyCount = WeeklyReport::whereIn('user_id', $barangayIds)->count();
        $monthlyCount = MonthlyReport::whereIn('user_id', $barangayIds)->count();
        $quarterlyCount = QuarterlyReport::whereIn('user_id', $barangayIds)->count();
        $annualCount = AnnualReport::whereIn('user_id', $barangayIds)->count();

        $reportCount = $weeklyCount + $monthlyCount + $quarterlyCount + $annualCount;

        // Get pending reports
        $pendingCount = WeeklyReport::whereIn('user_id', $barangayIds)
            ->where('status', 'pending')
            ->count() +
            MonthlyReport::whereIn('user_id', $barangayIds)
            ->where('status', 'pending')
            ->count() +
            QuarterlyReport::whereIn('user_id', $barangayIds)
            ->where('status', 'pending')
            ->count() +
            AnnualReport::whereIn('user_id', $barangayIds)
            ->where('status', 'pending')
            ->count();

        // Get overdue reports
        $overdueCount = DB::table('weekly_reports')
            ->join('report_types', 'weekly_reports.report_type_id', '=', 'report_types.id')
            ->whereIn('weekly_reports.user_id', $barangayIds)
            ->where('weekly_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->count() +
            DB::table('monthly_reports')
            ->join('report_types', 'monthly_reports.report_type_id', '=', 'report_types.id')
            ->whereIn('monthly_reports.user_id', $barangayIds)
            ->where('monthly_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->count() +
            DB::table('quarterly_reports')
            ->join('report_types', 'quarterly_reports.report_type_id', '=', 'report_types.id')
            ->whereIn('quarterly_reports.user_id', $barangayIds)
            ->where('quarterly_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->count() +
            DB::table('annual_reports')
            ->join('report_types', 'annual_reports.report_type_id', '=', 'report_types.id')
            ->whereIn('annual_reports.user_id', $barangayIds)
            ->where('annual_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->count();

        // Get recent submissions
        $recentSubmissions = collect();
        
        // Get recent weekly reports
        $weeklyReports = WeeklyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->latest()
            ->take(5)
            ->get();
        $recentSubmissions = $recentSubmissions->concat($weeklyReports);

        // Get recent monthly reports
        $monthlyReports = MonthlyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->latest()
            ->take(5)
            ->get();
        $recentSubmissions = $recentSubmissions->concat($monthlyReports);

        // Get recent quarterly reports
        $quarterlyReports = QuarterlyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->latest()
            ->take(5)
            ->get();
        $recentSubmissions = $recentSubmissions->concat($quarterlyReports);

        // Get recent annual reports
        $annualReports = AnnualReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->latest()
            ->take(5)
            ->get();
        $recentSubmissions = $recentSubmissions->concat($annualReports);

        // Sort all submissions by created_at and take the most recent 10
        $recentSubmissions = $recentSubmissions->sortByDesc('created_at')->take(10);

        // Add is_late property to each submission
        $recentSubmissions->each(function ($submission) {
            $submission->is_late = $submission->created_at->gt($submission->reportType->deadline);
        });

        if (auth()->user()->role === 'Facilitator') {
            return view('cluster.dashboard_facilitator', compact(
                'barangays',
                'barangayCount',
                'reportCount',
                'pendingCount',
                'overdueCount',
                'recentSubmissions',
                'weeklyCount',
                'monthlyCount',
                'quarterlyCount',
                'annualCount'
            ));
        }
        return view('cluster.dashboard', compact(
            'barangays',
            'barangayCount',
            'reportCount',
            'pendingCount',
            'overdueCount',
            'recentSubmissions',
            'weeklyCount',
            'monthlyCount',
            'quarterlyCount',
            'annualCount'
        ));
    }

    public function barangays()
    {
        $barangays = User::where('cluster_id', Auth::id())
            ->where('role', 'barangay')
            ->get();

        return view('cluster.barangays', compact('barangays'));
    }

    public function showBarangay($id)
    {
        $barangay = User::where('cluster_id', Auth::id())
            ->where('role', 'barangay')
            ->findOrFail($id);

        // Get all reports for this barangay
        $weeklyReports = WeeklyReport::with('reportType')
            ->where('user_id', $barangay->id)
            ->get();

        $monthlyReports = MonthlyReport::with('reportType')
            ->where('user_id', $barangay->id)
            ->get();

        $quarterlyReports = QuarterlyReport::with('reportType')
            ->where('user_id', $barangay->id)
            ->get();

        $annualReports = AnnualReport::with('reportType')
            ->where('user_id', $barangay->id)
            ->get();

        $reports = collect()
            ->concat($weeklyReports)
            ->concat($monthlyReports)
            ->concat($quarterlyReports)
            ->concat($annualReports)
            ->sortByDesc('created_at');

        return view('cluster.barangay-details', compact('barangay', 'reports'));
    }

    public function reports()
    {
        $clusterId = Auth::id();
        $cluster = User::find($clusterId);
        $barangays = $cluster->barangays;
        if ($cluster->parentCluster) {
            $barangays = $barangays->merge($cluster->parentCluster->barangays);
        }
        $barangayIds = $barangays->pluck('id');

        $weeklyReports = WeeklyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->get();

        $monthlyReports = MonthlyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->get();

        $quarterlyReports = QuarterlyReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->get();

        $annualReports = AnnualReport::with(['user', 'reportType'])
            ->whereIn('user_id', $barangayIds)
            ->get();

        $reports = collect()
            ->concat($weeklyReports)
            ->concat($monthlyReports)
            ->concat($quarterlyReports)
            ->concat($annualReports)
            ->sortByDesc('created_at');

        // Use different views for cluster and facilitator
        if (auth()->user()->role === 'Facilitator') {
            return view('cluster.reports', compact('reports'));
        } else {
        return view('cluster.reports', compact('reports'));
        }
    }

    public function showReport($id)
    {
        $report = null;
        $reportTypes = [
            'weekly' => WeeklyReport::class,
            'monthly' => MonthlyReport::class,
            'quarterly' => QuarterlyReport::class,
            'semestral' => SemestralReport::class,
            'annual' => AnnualReport::class
        ];

        foreach ($reportTypes as $type => $model) {
            $foundReport = $model::with(['user', 'reportType'])
                ->where('id', $id)
                ->first();
            if ($foundReport) {
                $report = $foundReport;
                break;
            }
        }

        if (!$report) {
            abort(404, 'Report not found');
        }

        // Improved authorization check
        $cluster = User::find(Auth::id());
        $barangayIds = $cluster->barangays->pluck('id');
        if ($cluster->parentCluster) {
            $barangayIds = $barangayIds->merge($cluster->parentCluster->barangays->pluck('id'));
        }
        // Debug log for authorization
        \Log::info('[ClusterController] Authorization debug', [
            'cluster_id' => $cluster->id,
            'cluster_role' => $cluster->role,
            'barangayIds' => $barangayIds->toArray(),
            'report_user_id' => $report->user_id,
            'report_id' => $report->id,
            'report_type' => get_class($report),
        ]);
        if (!$barangayIds->contains($report->user_id)) {
            abort(403, 'Unauthorized access to this report');
        }

        return view('cluster.report-details', compact('report'));
    }

    public function updateReport(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,resubmit',
            'remarks' => 'nullable|string|max:1000'
        ]);

        $report = null;
        $reportTypes = [
            'weekly' => WeeklyReport::class,
            'monthly' => MonthlyReport::class,
            'quarterly' => QuarterlyReport::class,
            'semestral' => SemestralReport::class,
            'annual' => AnnualReport::class
        ];

        foreach ($reportTypes as $type => $model) {
            $foundReport = $model::where('id', $id)->first();
            if ($foundReport) {
                $report = $foundReport;
                break;
            }
        }

        if (!$report) {
            return back()->with('error', 'Report not found');
        }

        // Improved authorization check
        $cluster = User::find(Auth::id());
        $barangayIds = $cluster->barangays->pluck('id');
        if ($cluster->parentCluster) {
            $barangayIds = $barangayIds->merge($cluster->parentCluster->barangays->pluck('id'));
        }
        if (!$barangayIds->contains($report->user_id)) {
            abort(403, 'Unauthorized access to this report');
        }

        // Prepare update data
        $updateData = [
            'status' => $request->status,
            'remarks' => $request->remarks
        ];

        // Remove frequency-specific validation and update logic for resubmit
        // Now, only status and remarks are updated for any report type

        $report->update($updateData);

        return back()->with('success', 'Report status updated successfully');
    }

    public function downloadFile($id)
    {
        try {
            $report = null;
            $reportTypes = [
                'weekly' => WeeklyReport::class,
                'monthly' => MonthlyReport::class,
                'quarterly' => QuarterlyReport::class,
                'semestral' => SemestralReport::class,
                'annual' => AnnualReport::class
            ];

            foreach ($reportTypes as $type => $model) {
                $foundReport = $model::where('id', $id)->first();
                if ($foundReport) {
                    $report = $foundReport;
                    break;
                }
            }

            if (!$report) {
                return back()->with('error', 'Report not found');
            }

            // Improved authorization check
            $cluster = User::find(Auth::id());
            $barangayIds = $cluster->barangays->pluck('id');
            if ($cluster->parentCluster) {
                $barangayIds = $barangayIds->merge($cluster->parentCluster->barangays->pluck('id'));
            }
            if (!$barangayIds->contains($report->user_id)) {
                abort(403, 'Unauthorized access to this file');
            }

            $path = storage_path('app/public/' . $report->file_path);

            if (!file_exists($path)) {
                return back()->with('error', 'File not found');
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
            $report = null;
            $reportTypes = [
                'weekly' => WeeklyReport::class,
                'monthly' => MonthlyReport::class,
                'quarterly' => QuarterlyReport::class,
                'semestral' => SemestralReport::class,
                'annual' => AnnualReport::class
            ];

            foreach ($reportTypes as $type => $model) {
                $foundReport = $model::where('id', $id)->first();
                if ($foundReport) {
                    $report = $foundReport;
                    break;
                }
            }

            if (!$report) {
                return back()->with('error', 'Report not found');
            }

            // Improved authorization check
            $cluster = User::find(Auth::id());
            $barangayIds = $cluster->barangays->pluck('id');
            if ($cluster->parentCluster) {
                $barangayIds = $barangayIds->merge($cluster->parentCluster->barangays->pluck('id'));
            }
            if (!$barangayIds->contains($report->user_id)) {
                abort(403, 'Unauthorized access to this file');
            }

            $path = storage_path('app/public/' . $report->file_path);

            if (!file_exists($path)) {
                return back()->with('error', 'File not found');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            \Log::error('File view error: ' . $e->getMessage());
            return back()->with('error', 'Failed to view file. Please try again.');
        }
    }

    public function reportTypes()
    {
        $reportTypes = \App\Models\ReportType::all();
        return view('cluster.report-types', compact('reportTypes'));
    }

    public function storeReportType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|string',
            'deadline' => 'required|date',
            'allowed_file_types' => 'nullable|array',
        ]);
        $data = $request->only(['name', 'description', 'frequency', 'deadline']);
        $data['allowed_file_types'] = $request->allowed_file_types;
        \App\Models\ReportType::create($data);
        return redirect()->route('cluster.report-types')->with('success', 'Report type created successfully.');
    }

    public function updateReportType(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|string',
            'deadline' => 'required|date',
            'allowed_file_types' => 'nullable|array',
        ]);
        $reportType = \App\Models\ReportType::findOrFail($id);
        $data = $request->only(['name', 'description', 'frequency', 'deadline']);
        $data['allowed_file_types'] = $request->allowed_file_types;
        $reportType->update($data);
        return redirect()->route('cluster.report-types')->with('success', 'Report type updated successfully.');
    }

    public function destroyReportType($id)
    {
        $reportType = \App\Models\ReportType::findOrFail($id);
        $reportType->delete();
        return redirect()->route('cluster.report-types')->with('success', 'Report type deleted successfully.');
    }

    public function showCluster($id)
    {
        $cluster = User::where('role', 'cluster')->findOrFail($id);
        // Get direct barangays
        $barangays = $cluster->barangays;
        // Get barangays under child clusters
        foreach ($cluster->childClusters as $child) {
            $barangays = $barangays->merge($child->barangays);
        }
        return view('cluster.show', compact('cluster', 'barangays'));
    }
} 