<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClusterController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\FacilitatorController;
use App\Http\Controllers\ReportSubmissionController;
use App\Http\Controllers\WeeklyReportController;
use App\Http\Controllers\ReportTypeController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BarangayFileController;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\{WeeklyReport, MonthlyReport, QuarterlyReport, SemestralReport, AnnualReport};
use App\Http\Controllers\AnnouncementController;

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Test Email Route
Route::get('/test-email', function() {
    try {
        \Illuminate\Support\Facades\Mail::raw('Test email from Laravel', function($message) {
            $message->to('jerel.paligumba10@gmail.com')
                    ->subject('Test Email from Laravel Route');
        });

        return 'Email sent successfully! Please check your inbox.';
    } catch (\Exception $e) {
        return 'Failed to send email: ' . $e->getMessage();
    }
});

// Check Email Configuration
Route::get('/check-email-config', function() {
    $config = [
        'MAIL_MAILER' => config('mail.default'),
        'MAIL_HOST' => config('mail.mailers.smtp.host'),
        'MAIL_PORT' => config('mail.mailers.smtp.port'),
        'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
        'MAIL_PASSWORD' => str_repeat('*', strlen(config('mail.mailers.smtp.password'))),
        'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
        'MAIL_FROM_ADDRESS' => config('mail.from.address'),
        'MAIL_FROM_NAME' => config('mail.from.name'),
    ];

    return '<h2>Email Configuration</h2><pre>' . json_encode($config, JSON_PRETTY_PRINT) . '</pre>';
});

// Test Email to Specific Barangay User
Route::get('/test-email-to-barangay/{userId}', function($userId) {
    try {
        // Get the barangay user
        $barangayUser = \App\Models\User::find($userId);

        if (!$barangayUser) {
            return 'User with ID ' . $userId . ' not found.';
        }

        // Display user details
        $details = "User: " . $barangayUser->name . " (ID: " . $barangayUser->id . ", Email: " . $barangayUser->email . ")<br>";

        // Send a direct email (not using notification)
        \Illuminate\Support\Facades\Mail::raw('This is a direct test email from Laravel to your barangay account. If you receive this, please inform the admin.', function($message) use ($barangayUser) {
            $message->to($barangayUser->email)
                    ->subject('Direct Test Email to Barangay Account');
        });

        return $details . '<br>Direct email sent successfully! Please check the inbox of ' . $barangayUser->email;
    } catch (\Exception $e) {
        return 'Failed to send email: ' . $e->getMessage() . '<br><pre>' . $e->getTraceAsString() . '</pre>';
    }
});

// Test Notification Route
Route::get('/test-notification', function() {
    try {
        // Get a barangay user
        $barangayUser = \App\Models\User::where('role', 'barangay')->first();

        if (!$barangayUser) {
            return 'No barangay user found to send notification to.';
        }

        // Get a report for testing
        $report = \App\Models\WeeklyReport::with('reportType')->first();

        if (!$report) {
            return 'No report found to use for notification.';
        }

        // Send notification
        $barangayUser->notify(new \App\Notifications\ReportRemarksNotification(
            $report,
            'This is a test remark from the notification test route.',
            'weekly',
            'Admin User'
        ));

        return 'Notification sent successfully to ' . $barangayUser->name . ' (' . $barangayUser->email . ')!';
    } catch (\Exception $e) {
        return 'Failed to send notification: ' . $e->getMessage() . '<br><pre>' . $e->getTraceAsString() . '</pre>';
    }
});

// List all barangay users and their reports
Route::get('/list-barangay-reports', function() {
    $barangayUsers = \App\Models\User::where('role', 'barangay')->get();

    if ($barangayUsers->isEmpty()) {
        return 'No barangay users found.';
    }

    $output = '<h2>Barangay Users and Their Reports</h2>';

    foreach ($barangayUsers as $user) {
        $output .= '<h3>User: ' . $user->name . ' (ID: ' . $user->id . ', Email: ' . $user->email . ')</h3>';

        // Get weekly reports
        $weeklyReports = \App\Models\WeeklyReport::with('reportType')
            ->where('user_id', $user->id)
            ->get();

        if ($weeklyReports->isNotEmpty()) {
            $output .= '<h4>Weekly Reports:</h4><ul>';
            foreach ($weeklyReports as $report) {
                $output .= '<li>ID: ' . $report->id . ' - ' . $report->reportType->name .
                    ' <a href="/test-notification-specific/' . $user->id . '/' . $report->id . '/weekly">Test Notification</a></li>';
            }
            $output .= '</ul>';
        }

        // Get monthly reports
        $monthlyReports = \App\Models\MonthlyReport::with('reportType')
            ->where('user_id', $user->id)
            ->get();

        if ($monthlyReports->isNotEmpty()) {
            $output .= '<h4>Monthly Reports:</h4><ul>';
            foreach ($monthlyReports as $report) {
                $output .= '<li>ID: ' . $report->id . ' - ' . $report->reportType->name .
                    ' <a href="/test-notification-specific/' . $user->id . '/' . $report->id . '/monthly">Test Notification</a></li>';
            }
            $output .= '</ul>';
        }

        // Get quarterly reports
        $quarterlyReports = \App\Models\QuarterlyReport::with('reportType')
            ->where('user_id', $user->id)
            ->get();

        if ($quarterlyReports->isNotEmpty()) {
            $output .= '<h4>Quarterly Reports:</h4><ul>';
            foreach ($quarterlyReports as $report) {
                $output .= '<li>ID: ' . $report->id . ' - ' . $report->reportType->name .
                    ' <a href="/test-notification-specific/' . $user->id . '/' . $report->id . '/quarterly">Test Notification</a></li>';
            }
            $output .= '</ul>';
        }

        // Get semestral reports
        $semestralReports = \App\Models\SemestralReport::with('reportType')
            ->where('user_id', $user->id)
            ->get();

        if ($semestralReports->isNotEmpty()) {
            $output .= '<h4>Semestral Reports:</h4><ul>';
            foreach ($semestralReports as $report) {
                $output .= '<li>ID: ' . $report->id . ' - ' . $report->reportType->name .
                    ' <a href="/test-notification-specific/' . $user->id . '/' . $report->id . '/semestral">Test Notification</a></li>';
            }
            $output .= '</ul>';
        }

        // Get annual reports
        $annualReports = \App\Models\AnnualReport::with('reportType')
            ->where('user_id', $user->id)
            ->get();

        if ($annualReports->isNotEmpty()) {
            $output .= '<h4>Annual Reports:</h4><ul>';
            foreach ($annualReports as $report) {
                $output .= '<li>ID: ' . $report->id . ' - ' . $report->reportType->name .
                    ' <a href="/test-notification-specific/' . $user->id . '/' . $report->id . '/annual">Test Notification</a></li>';
            }
            $output .= '</ul>';
        }
    }

    return $output;
});

// Test Notification with Specific User and Report
Route::get('/test-notification-specific/{userId}/{reportId}/{reportType}', function($userId, $reportId, $reportType) {
    try {
        // Get the specific barangay user
        $barangayUser = \App\Models\User::find($userId);

        if (!$barangayUser) {
            return 'Barangay user with ID ' . $userId . ' not found.';
        }

        // Get the specific report based on type
        $reportModel = null;
        switch ($reportType) {
            case 'weekly':
                $reportModel = \App\Models\WeeklyReport::class;
                break;
            case 'monthly':
                $reportModel = \App\Models\MonthlyReport::class;
                break;
            case 'quarterly':
                $reportModel = \App\Models\QuarterlyReport::class;
                break;
            case 'semestral':
                $reportModel = \App\Models\SemestralReport::class;
                break;
            case 'annual':
                $reportModel = \App\Models\AnnualReport::class;
                break;
            default:
                return 'Invalid report type: ' . $reportType;
        }

        $report = $reportModel::with('reportType')->find($reportId);

        if (!$report) {
            return 'Report with ID ' . $reportId . ' not found in ' . $reportType . ' reports.';
        }

        // Display user and report details
        $details = "User: " . $barangayUser->name . " (ID: " . $barangayUser->id . ", Email: " . $barangayUser->email . ")<br>";
        $details .= "Report: " . $report->reportType->name . " (ID: " . $report->id . ")<br>";

        // Send notification
        $barangayUser->notify(new \App\Notifications\ReportRemarksNotification(
            $report,
            'This is a test remark from the specific notification test route.',
            $reportType,
            'Admin User'
        ));

        return $details . '<br>Notification sent successfully!';
    } catch (\Exception $e) {
        return 'Failed to send notification: ' . $e->getMessage() . '<br><pre>' . $e->getTraceAsString() . '</pre>';
    }
});

// Protected Routes (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // User Management
        Route::get('/user-management', [AdminController::class, 'userManagement'])->name('user-management');
        Route::post('/users', [AdminController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [AdminController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{id}/confirm-deactivation', [AdminController::class, 'confirmDeactivation'])->name('confirm-deactivation');

        // Cluster Management
        Route::resource('clusters', ClusterController::class);

        // Add a route to create test clusters
        Route::get('/create-test-clusters', function() {
            if (App\Models\Cluster::count() === 0) {
                App\Models\Cluster::create(['name' => 'Cluster 1', 'description' => 'Test Cluster 1', 'is_active' => true]);
                App\Models\Cluster::create(['name' => 'Cluster 2', 'description' => 'Test Cluster 2', 'is_active' => true]);
                App\Models\Cluster::create(['name' => 'Cluster 3', 'description' => 'Test Cluster 3', 'is_active' => true]);
                App\Models\Cluster::create(['name' => 'Cluster 4', 'description' => 'Test Cluster 4', 'is_active' => true]);
                return 'Test clusters created successfully!';
            }
            return 'Clusters already exist!';
        });

        // Report Management
        Route::get('/view-submissions', [ReportController::class, 'index'])->name('view.submissions');
        Route::put('/reports/{id}', [ReportController::class, 'update'])->name('update.report');
        Route::post('/reports/{id}/approve', [ReportController::class, 'approve'])->name('approve.report');
        Route::post('/reports/{id}/reject', [ReportController::class, 'reject'])->name('reject.report');
        Route::get('/files/{id}', [ReportController::class, 'downloadFile'])->name('files.download');
        Route::post('/reports/{id}/resubmit', [ReportController::class, 'adminResubmit'])->name('reports.resubmit');

        // Report Types
        Route::get('/create-report', [ReportTypeController::class, 'index'])->name('create-report');
        Route::post('/report-types', [ReportTypeController::class, 'store'])->name('store-report');
        Route::get('/report-types/{id}/edit', [ReportTypeController::class, 'edit'])->name('edit-report');
        Route::put('/report-types/{id}', [ReportTypeController::class, 'update'])->name('update-report');
        Route::delete('/report-types/{id}', [ReportTypeController::class, 'destroy'])->name('destroy-report');

        // Admin Announcements Routes
        Route::resource('announcements', AnnouncementController::class);
        Route::put('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])->name('announcements.toggle-status');
    });

    // Barangay Routes
    Route::prefix('barangay')->name('barangay.')->middleware('auth')->group(function () {
        // Dashboard
        Route::get('/dashboard', [BarangayController::class, 'dashboard'])->name('dashboard');

        // Reports
        Route::get('/submit-report', [BarangayController::class, 'submitReport'])->name('submit-report');
        Route::post('/submissions/store', [BarangayController::class, 'store'])->name('submissions.store');
        Route::get('/submissions', [BarangayController::class, 'submissions'])->name('submissions');
        Route::get('/view-reports', [BarangayController::class, 'viewReports'])->name('view-reports');
        Route::get('/overdue-reports', [BarangayController::class, 'overdueReports'])->name('overdue-reports');
        Route::post('/submissions/{id}/resubmit', [BarangayController::class, 'resubmit'])->name('submissions.resubmit');

        // Debug routes for testing form submission
        Route::get('/test-resubmit', function() {
            return "Resubmit route is working!";
        })->name('test.resubmit');

        Route::get('/test-resubmit-form', function() {
            return view('barangay.test-resubmit');
        })->name('test.resubmit.form');

        // File Management
        Route::get('/files/{id}', [ReportController::class, 'downloadFile'])->name('files.download');
        Route::get('/direct-files/{id}', [BarangayController::class, 'directDownloadFile'])->name('direct.files.download');
        Route::delete('/files/{id}', [BarangayFileController::class, 'destroy'])->name('files.destroy');
    });

    // Facilitator Routes
    Route::prefix('facilitator')->name('facilitator.')->middleware(\App\Http\Middleware\FacilitatorMiddleware::class)->group(function () {
        // Dashboard
        Route::get('/dashboard', [FacilitatorController::class, 'dashboard'])->name('dashboard');

        // Report Viewing
        Route::get('/view-submissions', [FacilitatorController::class, 'viewSubmissions'])->name('view-submissions');
        Route::put('/reports/{id}/remarks', [FacilitatorController::class, 'addRemarks'])->name('reports.add-remarks');
        Route::post('/reports/{id}/approve', [FacilitatorController::class, 'approve'])->name('reports.approve');
        Route::post('/reports/{id}/resubmit', [FacilitatorController::class, 'resubmit'])->name('reports.resubmit');

        // File Download
        Route::get('/files/{id}', [BarangayFileController::class, 'download'])->name('files.download');
    });
});

