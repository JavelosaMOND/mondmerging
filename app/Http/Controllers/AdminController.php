<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cluster;
use Illuminate\Support\Facades\Hash;
use App\Models\ReportType;
use App\Models\WeeklyReport;
use App\Models\MonthlyReport;
use App\Models\QuarterlyReport;
use App\Models\AnnualReport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with users list.
     */
    public function index()
    {
        // Get total submissions count
        $totalSubmissions = WeeklyReport::count() +
                          MonthlyReport::count() +
                          QuarterlyReport::count() +
                          AnnualReport::count();

        // Get submitted reports count
        $submittedReports = WeeklyReport::where('status', 'submitted')->count() +
                            MonthlyReport::where('status', 'submitted')->count() +
                            QuarterlyReport::where('status', 'submitted')->count() +
                            AnnualReport::where('status', 'submitted')->count();

        // Get no submission reports count
        $noSubmissionReports = WeeklyReport::where('status', 'no submission')->count() +
                               MonthlyReport::where('status', 'no submission')->count() +
                               QuarterlyReport::where('status', 'no submission')->count() +
                               AnnualReport::where('status', 'no submission')->count();

        // Get late submissions count
        $lateSubmissions = DB::table('weekly_reports')
            ->join('report_types', 'weekly_reports.report_type_id', '=', 'report_types.id')
            ->where('weekly_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->where('weekly_reports.status', 'submitted')
            ->count() +
            DB::table('monthly_reports')
            ->join('report_types', 'monthly_reports.report_type_id', '=', 'report_types.id')
            ->where('monthly_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->where('monthly_reports.status', 'submitted')
            ->count() +
            DB::table('quarterly_reports')
            ->join('report_types', 'quarterly_reports.report_type_id', '=', 'report_types.id')
            ->where('quarterly_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->where('quarterly_reports.status', 'submitted')
            ->count() +
            DB::table('annual_reports')
            ->join('report_types', 'annual_reports.report_type_id', '=', 'report_types.id')
            ->where('annual_reports.created_at', '>', DB::raw('report_types.deadline'))
            ->where('annual_reports.status', 'submitted')
            ->count();

        // Get recent submissions
        $recentSubmissions = DB::table('weekly_reports')
            ->select([
                'weekly_reports.id',
                'weekly_reports.user_id',
                'weekly_reports.report_type_id',
                'weekly_reports.status',
                'weekly_reports.created_at as submitted_at',
                'users.name as submitter',
                'report_types.name as report_type',
                DB::raw('CASE WHEN weekly_reports.status = "pending" AND weekly_reports.created_at > report_types.deadline THEN true ELSE false END as is_late')
            ])
            ->join('users', 'weekly_reports.user_id', '=', 'users.id')
            ->join('report_types', 'weekly_reports.report_type_id', '=', 'report_types.id')
            ->union(
                DB::table('monthly_reports')
                ->select([
                    'monthly_reports.id',
                    'monthly_reports.user_id',
                    'monthly_reports.report_type_id',
                    'monthly_reports.status',
                    'monthly_reports.created_at as submitted_at',
                    'users.name as submitter',
                    'report_types.name as report_type',
                    DB::raw('CASE WHEN monthly_reports.status = "submitted" AND monthly_reports.created_at > report_types.deadline THEN true ELSE false END as is_late')
                ])
                ->join('users', 'monthly_reports.user_id', '=', 'users.id')
                ->join('report_types', 'monthly_reports.report_type_id', '=', 'report_types.id')
            )
            ->union(
                DB::table('quarterly_reports')
                ->select([
                    'quarterly_reports.id',
                    'quarterly_reports.user_id',
                    'quarterly_reports.report_type_id',
                    'quarterly_reports.status',
                    'quarterly_reports.created_at as submitted_at',
                    'users.name as submitter',
                    'report_types.name as report_type',
                    DB::raw('CASE WHEN quarterly_reports.status = "submitted" AND quarterly_reports.created_at > report_types.deadline THEN true ELSE false END as is_late')
                ])
                ->join('users', 'quarterly_reports.user_id', '=', 'users.id')
                ->join('report_types', 'quarterly_reports.report_type_id', '=', 'report_types.id')
            )
            ->union(
                DB::table('annual_reports')
                ->select([
                    'annual_reports.id',
                    'annual_reports.user_id',
                    'annual_reports.report_type_id',
                    'annual_reports.status',
                    'annual_reports.created_at as submitted_at',
                    'users.name as submitter',
                    'report_types.name as report_type',
                    DB::raw('CASE WHEN annual_reports.status = "submitted" AND annual_reports.created_at > report_types.deadline THEN true ELSE false END as is_late')
                ])
                ->join('users', 'annual_reports.user_id', '=', 'users.id')
                ->join('report_types', 'annual_reports.report_type_id', '=', 'report_types.id')
            )
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get();

        // Get submissions by type for chart
        $weeklyCount = WeeklyReport::count();
        $monthlyCount = MonthlyReport::count();
        $quarterlyCount = QuarterlyReport::count();
        $annualCount = AnnualReport::count();

        return view('admin.dashboard', compact(
            'totalSubmissions',
            'submittedReports',
            'noSubmissionReports',
            'lateSubmissions',
            'recentSubmissions',
            'weeklyCount',
            'monthlyCount',
            'quarterlyCount',
            'annualCount'
        ));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'user_type' => 'required|in:admin,facilitator,barangay',
            'cluster_id' => 'nullable|exists:clusters,id',
            'clusters' => 'nullable|array',
            'clusters.*' => 'exists:clusters,id',
        ]);

        if ($request->user_type === 'barangay') {
            $clusterExists = Cluster::where('is_active', true)->exists();
            if (!$clusterExists) {
                return back()->with('error', 'At least one active cluster must exist before adding a barangay.');
            }

            if (!$request->cluster_id) {
                return back()->with('error', 'Barangays must be assigned to a cluster.');
            }

            // Verify that the selected cluster is active
            $cluster = Cluster::find($request->cluster_id);
            if (!$cluster || !$cluster->is_active) {
                return back()->with('error', 'Please select a valid active cluster.');
            }
        }

        DB::beginTransaction();
        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->user_type, // For backward compatibility
                'user_type' => $request->user_type,
                'cluster_id' => $request->user_type === 'barangay' ? $request->cluster_id : null,
            ]);

            // If the user is a facilitator, assign them to the selected clusters
            if ($request->user_type === 'facilitator' && $request->has('clusters')) {
                foreach ($request->clusters as $clusterId) {
                    DB::table('facilitator_cluster')->insert([
                        'user_id' => $user->id,
                        'cluster_id' => $clusterId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', ucfirst($request->user_type) . ' account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Confirm deactivation of a user.
     */
    public function confirmDeactivation($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'cluster') {
            $barangayExists = User::where('role', 'barangay')
                ->where('cluster_id', $user->id)
                ->exists();

            if ($barangayExists) {
                return response()->json([
                    'confirm' => $user->is_active
                        ? 'This cluster has assigned barangays. Are you sure you want to deactivate it?'
                        : 'This cluster has assigned barangays. Are you sure you want to reactivate it?'
                ]);
            }
        }

        return response()->json([
            'confirm' => $user->is_active
                ? 'Are you sure you want to deactivate this barangay?'
                : 'Are you sure you want to reactivate this barangay?'
        ]);
    }

    /**
     * Deactivate or activate a user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', ucfirst($user->role) . ' status updated to ' . ($user->is_active ? 'active' : 'inactive') . '.');
    }

    /**
     * Display the user management page.
     */
    public function userManagement()
    {
        $users = User::with(['cluster', 'assignedClusters'])->get();

        // Add assigned_clusters property to each user for easier access in the view
        $users->each(function ($user) {
            $user->assigned_clusters = $user->assignedClusters->pluck('id')->toArray();
        });

        return view('admin.user-management', compact('users'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'user_type' => 'required|in:admin,facilitator,barangay',
            'cluster_id' => 'nullable|exists:clusters,id',
            'clusters' => 'nullable|array',
            'clusters.*' => 'exists:clusters,id',
        ]);

        if ($request->user_type === 'barangay') {
            if (!$request->cluster_id) {
                return back()->with('error', 'Barangays must be assigned to a cluster.');
            }

            // Verify that the selected cluster is active
            $cluster = Cluster::find($request->cluster_id);
            if (!$cluster || !$cluster->is_active) {
                return back()->with('error', 'Please select a valid active cluster.');
            }
        }

        DB::beginTransaction();
        try {
            // Update the user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->user_type, // For backward compatibility
                'user_type' => $request->user_type,
                'cluster_id' => $request->user_type === 'barangay' ? $request->cluster_id : null,
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'required|min:6',
                ]);
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
            }

            // If the user is a facilitator, update their cluster assignments
            if ($request->user_type === 'facilitator') {
                // Remove existing assignments
                DB::table('facilitator_cluster')->where('user_id', $user->id)->delete();

                // Add new assignments
                if ($request->has('clusters')) {
                    foreach ($request->clusters as $clusterId) {
                        DB::table('facilitator_cluster')->insert([
                            'user_id' => $user->id,
                            'cluster_id' => $clusterId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function viewSubmissions(Request $request)
    {
        $query = Submission::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('report_type', 'like', "%{$search}%")
                  ->orWhere('submitted_by', 'like', "%{$search}%");
            });
        }

        // Handle type filter
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Handle status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Handle timeliness filter
        if ($request->has('timeliness') && !empty($request->timeliness)) {
            if ($request->timeliness === 'late') {
                $query->where('is_late', true);
            } else if ($request->timeliness === 'ontime') {
                $query->where('is_late', false);
            }
        }

        $submissions = $query->paginate(10)->withQueryString();

        return view('admin.view-submissions', compact('submissions'));
    }
}
