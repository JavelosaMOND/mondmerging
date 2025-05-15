<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClusterController;
use App\Http\Controllers\BarangayController;
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

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/profile/update', [AdminController::class, 'updateProfile'])->name('profile.update');

        // Report Management
        Route::get('/view-submissions', [ReportController::class, 'index'])->name('view.submissions');
        Route::put('/reports/{id}', [ReportController::class, 'update'])->name('update.report');
        Route::get('/files/{id}', [ReportController::class, 'downloadFile'])->name('files.download');

        // Report Types
        Route::get('/create-report', [ReportTypeController::class, 'index'])->name('create-report');
        Route::post('/report-types', [ReportTypeController::class, 'store'])->name('store-report');
        Route::get('/report-types/{id}/edit', [ReportTypeController::class, 'edit'])->name('edit-report');
        Route::put('/report-types/{id}', [ReportTypeController::class, 'update'])->name('update-report');
        Route::delete('/report-types/{id}', [ReportTypeController::class, 'destroy'])->name('destroy-report');
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
<<<<<<< Updated upstream
        Route::post('/submissions/{id}/resubmit', [BarangayController::class, 'resubmit'])->name('submissions.resubmit');
=======
        Route::post('/submissions/resubmit', [BarangayController::class, 'resubmit'])->name('submissions.resubmit');
        Route::post('/change-password', [BarangayController::class, 'changePassword'])->name('change-password');
>>>>>>> Stashed changes

        // File Management
        Route::get('/files/{id}', [ReportController::class, 'downloadFile'])->name('files.download');
        Route::delete('/files/{id}', [BarangayFileController::class, 'destroy'])->name('files.destroy');
    });

    // Cluster Routes
    Route::prefix('cluster')->name('cluster.')->middleware(['auth', 'role:cluster'])->group(function () {
        // Dashboard
        Route::get('/', [ClusterController::class, 'index'])->name('index');
        Route::get('/dashboard', [ClusterController::class, 'dashboard'])->name('dashboard');

        // Barangays
        Route::get('/barangays', [ClusterController::class, 'barangays'])->name('barangays');
        Route::get('/barangays/{id}', [ClusterController::class, 'showBarangay'])->name('barangays.show');

        // Reports
        Route::get('/reports', [ClusterController::class, 'reports'])->name('reports');
        Route::get('/reports/{id}', [ClusterController::class, 'showReport'])->name('reports.show');
        Route::put('/reports/{id}', [ClusterController::class, 'updateReport'])->name('reports.update');
        Route::get('/files/{id}/download', [ClusterController::class, 'downloadFile'])->name('files.download');
        Route::get('/files/{id}/view', [ClusterController::class, 'viewFile'])->name('files.view');

        // Report Types
        Route::get('/report-types', [ClusterController::class, 'reportTypes'])->name('report-types');
        Route::post('/report-types', [ClusterController::class, 'storeReportType'])->name('report-types.store');
        Route::put('/report-types/{id}', [ClusterController::class, 'updateReportType'])->name('report-types.update');
        Route::delete('/report-types/{id}', [ClusterController::class, 'destroyReportType'])->name('report-types.destroy');
    });
});

