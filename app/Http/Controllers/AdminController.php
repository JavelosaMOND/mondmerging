<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ReportType;
use App\Models\WeeklyReport;
use App\Models\MonthlyReport;
use App\Models\QuarterlyReport;
use App\Models\AnnualReport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Submission;

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
            'user_id' => 'required|string|max:255|unique:users,email',
            'role' => 'required|in:cluster,barangay',
            'cluster_id' => 'nullable|exists:users,id',
        ]);

        if ($request->role === 'barangay') {
            $clusterExists = User::where('role', 'cluster')->exists();
            if (!$clusterExists) {
                return back()->with('error', 'A cluster must be created before adding a barangay.');
            }

            if (!$request->cluster_id) {
                return back()->with('error', 'Barangays must be assigned to a cluster.');
            }
        }

        $userId = strtoupper($request->user_id);
        $email = $userId . '@dilgbacolod.com';
        $password = 'DILGBACOLOD' . $userId;

        User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => \Hash::make($password),
            'role' => $request->role,
            'cluster_id' => $request->cluster_id,
        ]);

        return back()->with('success', ucfirst($request->role) . ' account created successfully.');
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
        $users = User::with('cluster')->get();
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
            'role' => 'required|in:cluster,barangay',
            'cluster_id' => 'nullable|exists:users,id',
        ]);

        if ($request->role === 'barangay') {
            if (!$request->cluster_id) {
                return back()->with('error', 'Barangays must be assigned to a cluster.');
            }
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'cluster_id' => $request->cluster_id,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|min:6',
            ]);
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return back()->with('success', 'User updated successfully.');
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

    /**
     * Reset the user's password to the default format.
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        // Extract USER_ID from email (before @)
        $userId = strtoupper(explode('@', $user->email)[0]);
        $defaultPassword = 'DILGBACOLOD' . $userId;
        $user->password = \Hash::make($defaultPassword);
        $user->save();
        return response()->json(['message' => 'Password reset to default: DILGBACOLOD' . $userId]);
    }

    /**
     * Update the authenticated admin's profile (AJAX from profile modal).
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // If password fields are filled, validate and update password
        if ($request->filled('current_password') || $request->filled('new_password') || $request->filled('new_password_confirmation')) {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);
            if (!\Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
            }
            $user->password = \Hash::make($request->new_password);
        }

        $user->save();
        return response()->json(['success' => true, 'message' => 'Profile updated successfully.', 'reload' => true]);
    }
}
