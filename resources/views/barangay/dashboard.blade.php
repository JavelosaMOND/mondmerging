@extends('layouts.barangay')

@section('title', 'Barangay Dashboard')

@section('content')
<div class="dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <h1 class="h3 mb-2 text-primary">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-secondary">Here's an overview of your reports and upcoming deadlines.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt text-info"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ $totalReports }}</h3>
                    <p class="stat-label">Total Reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ $submittedReports }}</h3>
                    <p class="stat-label">Submitted Reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ $noSubmissionReports }}</h3>
                    <p class="stat-label">No Submission Reports</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitReportModal">
                <i class="fas fa-plus-circle me-2"></i>
                Submit New Report
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Recent Reports -->
        <div class="col-md-6">
            <div class="card h-100 recent-reports-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-history me-2 text-primary"></i>
                        <h5 class="card-title mb-0">Recent Reports</h5>
                    </div>
                    <a href="{{ route('barangay.submissions') }}" class="btn-view-all">
                        View All
                        <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentReports->isEmpty())
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <p>No recent reports</p>
                            <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#submitReportModal">
                                <i class="fas fa-plus-circle me-1"></i>
                                Submit Your First Report
                            </button>
                        </div>
                    @else
                        <div class="report-list">
                            @foreach($recentReports as $report)
                                <div class="report-item">
                                    <div class="report-icon">
                                        @php
                                            $iconClass = match($report->reportType->frequency) {
                                                'weekly' => 'fa-calendar-week text-info',
                                                'monthly' => 'fa-calendar-alt text-primary',
                                                'quarterly' => 'fa-calendar-check text-success',
                                                'semestral' => 'fa-calendar-plus text-warning',
                                                'annual' => 'fa-calendar text-danger',
                                                default => 'fa-file-alt text-secondary'
                                            };
                                        @endphp
                                        <i class="fas {{ $iconClass }}"></i>
                                    </div>
                                    <div class="report-content">
                                        <h6 class="report-title">{{ $report->reportType->name }}</h6>
                                        <div class="report-meta">
                                            <span class="report-date">
                                                <i class="far fa-clock me-1"></i>
                                                {{ $report->created_at->format('M d, Y') }}
                                            </span>
                                            <span class="report-status status-{{ $report->status }}">
                                                <i class="fas {{ $report->status === 'submitted' ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-1"></i>
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="col-md-6">
            <div class="card h-100 upcoming-deadlines-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        <h5 class="card-title mb-0">Upcoming Deadlines</h5>
                    </div>
                    <a href="{{ route('barangay.overdue-reports') }}" class="btn-view-all">
                        View All
                        <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($upcomingDeadlines->isEmpty())
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <p>No upcoming deadlines</p>
                            <span class="text-muted mt-1 small">You're all caught up!</span>
                        </div>
                    @else
                        <div class="deadline-list-container">
                            <div class="deadline-list">
                                @foreach($upcomingDeadlines as $deadline)
                                    <div class="deadline-item">
                                        <div class="deadline-info">
                                            <div class="deadline-icon">
                                                @php
                                                    $iconClass = match($deadline->frequency) {
                                                        'weekly' => 'fa-calendar-week text-info',
                                                        'monthly' => 'fa-calendar-alt text-primary',
                                                        'quarterly' => 'fa-calendar-check text-success',
                                                        'semestral' => 'fa-calendar-plus text-warning',
                                                        'annual' => 'fa-calendar text-danger',
                                                        default => 'fa-calendar-day text-secondary'
                                                    };

                                                    // Check if this report type has been submitted
                                                    $isSubmitted = $submittedReportTypeIds->contains($deadline->id);
                                                @endphp
                                                <i class="fas {{ $iconClass }}"></i>
                                            </div>
                                            <div class="deadline-content">
                                                <h6 class="deadline-title">{{ $deadline->name }}</h6>
                                                <div class="deadline-meta">
                                                    <span class="deadline-date">
                                                        <i class="far fa-clock me-1"></i>
                                                        Due: {{ $deadline->deadline->format('M d, Y') }}
                                                    </span>
                                                    <span class="deadline-frequency frequency-{{ $deadline->frequency }}">
                                                        {{ ucfirst($deadline->frequency) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button"
                                                class="btn-submit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#submitReportModal"
                                                data-report-type="{{ $deadline->id }}"
                                                data-frequency="{{ $deadline->frequency }}">
                                            <i class="fas fa-file-upload me-1"></i>
                                            Submit
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @if(count($upcomingDeadlines) > 5)
                                <div class="scroll-indicator">
                                    <i class="fas fa-chevron-down"></i>
                                    <span>Scroll for more</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-rgb: 13, 110, 253;
    --success-rgb: 25, 135, 84;
    --warning-rgb: 255, 193, 7;
    --danger-rgb: 220, 53, 69;
    --info-rgb: 13, 202, 240;
    --secondary-rgb: 108, 117, 125;
    --gray-rgb: 173, 181, 189;
    --primary-dark: #0b5ed7;
}

.dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

.welcome-section {
    padding: 1rem 0;
}

.stat-card {
    background: white;
    border-radius: var(--radius-md);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0;
    line-height: 1.2;
}

.stat-label {
    color: var(--gray-600);
    margin: 0;
    font-size: 0.875rem;
}

.card {
    border: none;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: var(--shadow-md);
}

.card-header {
    background: white;
    border-bottom: 1px solid var(--gray-200);
    padding: 1.25rem;
}

.card-title {
    color: var(--gray-800);
    font-weight: 600;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid var(--gray-200);
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}

.list-group-item:last-child {
    border-bottom: none;
}

.list-group-item:hover {
    background: var(--gray-100);
}

.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    font-size: 0.75rem;
}

.btn-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.btn-link:hover {
    color: var(--primary);
    text-decoration: underline;
}

.bg-primary-light {
    background: var(--primary-light);
}

.bg-success-light {
    background: var(--success-light);
}

.bg-warning-light {
    background: var(--warning-light);
}

.bg-danger-light {
    background: var(--danger-light);
}

/* Frequency Badge Colors */
.badge.bg-info {
    background-color: #0dcaf0 !important;
    color: #fff;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
    color: #fff;
}

.badge.bg-success {
    background-color: #198754 !important;
    color: #fff;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: #fff;
}

/* Drop Zone Styles */
.drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.drop-zone:hover {
    border-color: #0d6efd;
    background-color: #f1f3f5;
}

.drop-zone.dragover {
    border-color: #0d6efd;
    background-color: #e7f5ff;
}

.drop-zone__input {
    display: none;
}

.drop-zone__thumb {
    width: 100%;
    height: 100%;
    border-radius: 0.25rem;
    overflow: hidden;
    background-color: #cccccc;
    background-size: cover;
    position: relative;
}

.drop-zone__thumb::after {
    content: attr(data-label);
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 0.5rem 0;
    color: #ffffff;
    background: rgba(0, 0, 0, 0.75);
    font-size: 0.875rem;
    text-align: center;
}

.drop-zone__prompt {
    color: #6c757d;
}

.drop-zone__prompt i {
    color: #0d6efd;
}

/* Modern Recent Reports Styles */
.recent-reports-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.recent-reports-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.recent-reports-card .card-header {
    padding: 1.25rem 1.5rem;
    background: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.btn-view-all {
    color: var(--primary);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.btn-view-all:hover {
    color: var(--primary);
    transform: translateX(2px);
}

.btn-view-all i {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
}

.btn-view-all:hover i {
    transform: translateX(2px);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
}

.empty-state-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(var(--primary-rgb), 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: var(--primary);
    font-size: 1.5rem;
}

.empty-state p {
    color: var(--gray-600);
    margin-bottom: 0;
}

.report-list {
    padding: 0.5rem 0;
}

.report-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.report-item:last-child {
    border-bottom: none;
}

.report-item:hover {
    background-color: rgba(var(--primary-rgb), 0.02);
}

.report-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(var(--primary-rgb), 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.25rem;
}

.report-content {
    flex: 1;
}

.report-title {
    font-size: 0.9375rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--gray-800);
}

.report-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.8125rem;
}

.report-date {
    color: var(--gray-600);
    display: flex;
    align-items: center;
}

.report-status {
    display: flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.75rem;
}

.report-status.status-submitted {
    background-color: rgba(var(--success-rgb), 0.1);
    color: var(--success);
}

.report-status.status-no-submission {
    background-color: rgba(var(--danger-rgb), 0.1);
    color: var(--danger);
}

.report-status.status-pending {
    background-color: rgba(var(--warning-rgb), 0.1);
    color: var(--warning);
}

/* Upcoming Deadlines Styles */
.upcoming-deadlines-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.upcoming-deadlines-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.upcoming-deadlines-card .card-header {
    padding: 1.25rem 1.5rem;
    background: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.deadline-list {
    padding: 0.5rem 0;
    max-height: 400px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.deadline-list::-webkit-scrollbar {
    width: 6px;
}

.deadline-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.deadline-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.deadline-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.deadline-list-container {
    position: relative;
}

.scroll-indicator {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,0.9) 50%, rgba(255,255,255,1) 100%);
    padding: 30px 0 10px;
    text-align: center;
    font-size: 0.8rem;
    color: #6c757d;
    display: flex;
    flex-direction: column;
    align-items: center;
    pointer-events: none;
    animation: pulse 2s infinite;
}

.scroll-indicator i {
    margin-bottom: 5px;
}

@keyframes pulse {
    0% {
        opacity: 0.7;
    }
    50% {
        opacity: 1;
    }
    100% {
        opacity: 0.7;
    }
}

.deadline-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.deadline-item:last-child {
    border-bottom: none;
}

.deadline-item:hover {
    background-color: rgba(var(--primary-rgb), 0.02);
}

.deadline-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.deadline-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(var(--primary-rgb), 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.25rem;
}

.deadline-content {
    flex: 1;
}

.deadline-title {
    font-size: 0.9375rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--gray-800);
}

.deadline-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.8125rem;
}

.deadline-date {
    color: var(--gray-600);
    display: flex;
    align-items: center;
}

.deadline-frequency {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.75rem;
}

.frequency-weekly {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.frequency-monthly {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.frequency-quarterly {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.frequency-semestral {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.frequency-annual {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.btn-submit {
    background-color: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.2);
}

.btn-submit:hover {
    background-color: var(--primary-dark, #0b5ed7);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.3);
}

.btn-submit:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(var(--primary-rgb), 0.2);
}
</style>

<!-- Submit Report Modal -->
<div class="modal fade" id="submitReportModal" tabindex="-1" aria-labelledby="submitReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitReportModalLabel">
                    <i class="fas fa-file-alt me-2"></i>
                    Submit New Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="submitReportForm" method="POST" action="{{ route('barangay.submissions.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="frequency_filter" class="form-label">
                                    Filter by Frequency
                                </label>
                                <select id="frequency_filter" class="form-select">
                                    <option value="">All Frequencies</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="annual">Annual</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="report_type" class="form-label">
                                    Report Type
                                </label>
                                <select id="report_type" class="form-select @error('report_type_id') is-invalid @enderror" name="report_type_id" required>
                                    <option value="">Select Report Type</option>
                                    @foreach($reportTypes as $reportType)
                                        <option value="{{ $reportType->id }}" data-frequency="{{ $reportType->frequency }}">
                                            {{ $reportType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="no-report-types-message" class="alert alert-info mt-2" style="display: none;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You have already submitted all available reports for this frequency.
                                </div>

                                @if($availableReportTypeCounts['total'] == 0)
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-check-circle me-2"></i>
                                    You have already submitted all available reports. Check your submissions for any reports that need resubmission.
                                </div>
                                @endif

                                <!-- Debug information for submitted report types (hidden) -->
                                <div class="d-none">
                                    <p>Submitted Report Type IDs:
                                        @foreach($submittedReportTypeIds as $id)
                                            {{ $id }},
                                        @endforeach
                                    </p>
                                    <p>Available Report Types by Frequency:</p>
                                    <ul>
                                        @foreach($reportTypesByFrequency as $frequency => $types)
                                            <li>{{ $frequency }}: {{ $types->count() }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @error('report_type_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="file" class="form-label">
                                    Upload File
                                </label>
                                <div class="drop-zone" id="dropZone">
                                    <div class="drop-zone__prompt">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
                                        <p class="mb-1">Drag and drop your file here or click to browse</p>
                                        <small class="text-muted">Accepted formats: PDF, DOC, DOCX, XLSX (Max: 2MB)</small>
                                    </div>
                                    <input type="file" name="file" id="file" class="drop-zone__input" accept=".pdf,.doc,.docx,.xlsx" required>
                                </div>
                                @error('file')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Report Fields -->
                    <div id="weekly-fields" class="report-fields" style="display: none;">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-4">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Report Period
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Month</label>
                                        <select class="form-select" name="month" required>
                                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                <option value="{{ $month }}">{{ $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Week Number</label>
                                        <input type="number" class="form-control" name="week_number" min="1" max="52" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-4">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Report Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Number of Clean-up Sites</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="num_of_clean_up_sites" min="0" required>
                                            <span class="input-group-text">sites</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Number of Participants</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="num_of_participants" min="0" required>
                                            <span class="input-group-text">people</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Number of Barangays</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="num_of_barangays" min="0" required>
                                            <span class="input-group-text">barangays</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Total Volume</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="total_volume" min="0" step="0.01" required>
                                            <span class="input-group-text">m³</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Report Fields -->
                    <div id="monthly-fields" class="report-fields" style="display: none;">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-4">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Report Period
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label">Month</label>
                                    <select class="form-select" name="month" required>
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quarterly Report Fields -->
                    <div id="quarterly-fields" class="report-fields" style="display: none;">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-4">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Report Period
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label">Quarter Number</label>
                                    <select class="form-select" name="quarter_number" required>
                                        <option value="1">First Quarter (Jan-Mar)</option>
                                        <option value="2">Second Quarter (Apr-Jun)</option>
                                        <option value="3">Third Quarter (Jul-Sep)</option>
                                        <option value="4">Fourth Quarter (Oct-Dec)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Semestral Report Fields -->
                    <div id="semestral-fields" class="report-fields" style="display: none;">
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-4">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Report Period
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label">Semester Number</label>
                                    <select class="form-select" name="sem_number" required>
                                        <option value="1">First Semester (Jan-Jun)</option>
                                        <option value="2">Second Semester (Jul-Dec)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>
                            Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Report type change handler
    const reportTypeSelect = document.getElementById('report_type');
    const frequencyFilter = document.getElementById('frequency_filter');
    const weeklyFields = document.getElementById('weekly-fields');
    const monthlyFields = document.getElementById('monthly-fields');
    const quarterlyFields = document.getElementById('quarterly-fields');
    const semestralFields = document.getElementById('semestral-fields');

    // Function to filter report types based on frequency
    function filterReportTypes() {
        const selectedFrequency = frequencyFilter.value;
        const options = reportTypeSelect.options;
        let visibleOptions = 0;

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === '') continue; // Skip the default option

            if (!selectedFrequency || option.dataset.frequency === selectedFrequency) {
                option.style.display = '';
                visibleOptions++;
            } else {
                option.style.display = 'none';
            }
        }

        // Reset report type selection if the current selection doesn't match the filter
        if (reportTypeSelect.value) {
            const selectedOption = reportTypeSelect.options[reportTypeSelect.selectedIndex];
            if (selectedFrequency && selectedOption.dataset.frequency !== selectedFrequency) {
                reportTypeSelect.value = '';
                hideAllFields();
            }
        }

        // Show a message if no options are available after filtering
        const noOptionsMessage = document.getElementById('no-report-types-message');
        if (noOptionsMessage) {
            if (visibleOptions === 0) {
                noOptionsMessage.style.display = 'block';
                if (selectedFrequency) {
                    noOptionsMessage.innerHTML = `
                        <i class="fas fa-info-circle me-2"></i>
                        You have already submitted all available ${selectedFrequency} reports.
                    `;
                } else {
                    noOptionsMessage.innerHTML = `
                        <i class="fas fa-info-circle me-2"></i>
                        You have already submitted all available reports.
                    `;
                }
            } else {
                noOptionsMessage.style.display = 'none';
            }
        }
    }

    // Function to hide all report fields
    function hideAllFields() {
        weeklyFields.style.display = 'none';
        monthlyFields.style.display = 'none';
        quarterlyFields.style.display = 'none';
        semestralFields.style.display = 'none';
    }

    // Add event listener for frequency filter
    frequencyFilter.addEventListener('change', filterReportTypes);

    reportTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const frequency = selectedOption.dataset.frequency;

        // Hide all fields first
        hideAllFields();

        // Show relevant fields
        switch (frequency) {
            case 'weekly':
                weeklyFields.style.display = 'block';
                break;
            case 'monthly':
                monthlyFields.style.display = 'block';
                break;
            case 'quarterly':
                quarterlyFields.style.display = 'block';
                break;
            case 'semestral':
                semestralFields.style.display = 'block';
                break;
        }
    });

    // Handle submit buttons in upcoming deadlines
    const submitButtons = document.querySelectorAll('[data-bs-target="#submitReportModal"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reportTypeId = this.dataset.reportType;
            const frequency = this.dataset.frequency;

            // Set the frequency filter
            frequencyFilter.value = frequency;
            filterReportTypes();

            // Set the report type
            reportTypeSelect.value = reportTypeId;

            // Trigger the change event to show appropriate fields
            const event = new Event('change');
            reportTypeSelect.dispatchEvent(event);
        });
    });

    // Initial filter application
    filterReportTypes();

    // Handle scroll indicator for upcoming deadlines
    const deadlineList = document.querySelector('.deadline-list');
    const scrollIndicator = document.querySelector('.scroll-indicator');

    if (deadlineList && scrollIndicator) {
        deadlineList.addEventListener('scroll', function() {
            // If user has scrolled down, hide the indicator
            if (deadlineList.scrollTop > 50) {
                scrollIndicator.style.opacity = '0';
                scrollIndicator.style.transition = 'opacity 0.5s ease';
            } else {
                scrollIndicator.style.opacity = '1';
            }
        });
    }

    // Check if there are any report types available
    const hasReportTypes = Array.from(reportTypeSelect.options).some(option =>
        option.value !== '' && option.style.display !== 'none'
    );

    if (!hasReportTypes) {
        const noOptionsMessage = document.getElementById('no-report-types-message');
        if (noOptionsMessage) {
            noOptionsMessage.style.display = 'block';
            noOptionsMessage.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                You have already submitted all available reports.
            `;
        }

        // Disable the submit button if no report types are available
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('btn-secondary');
            submitBtn.classList.remove('btn-primary');
        }
    }

    // Enhanced Drop zone functionality
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.querySelector('.drop-zone__input');
    const form = document.getElementById('submitReportForm');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.add('dragover');
    });

    ['dragleave', 'dragend'].forEach(type => {
        dropZone.addEventListener(type, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('dragover');

        if (e.dataTransfer.files.length) {
            const file = e.dataTransfer.files[0];
            if (validateFile(file)) {
                fileInput.files = e.dataTransfer.files;
                updateThumbnail(dropZone, file);
            }
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            const file = fileInput.files[0];
            if (validateFile(file)) {
                updateThumbnail(dropZone, file);
            } else {
                fileInput.value = '';
                resetDropZone();
            }
        }
    });

    function validateFile(file) {
        const validTypes = ['.pdf', '.doc', '.docx', '.xlsx'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        if (!validTypes.includes(fileExtension)) {
            alert('Invalid file type. Please upload PDF, DOC, DOCX, or XLSX files only.');
            return false;
        }

        if (file.size > maxSize) {
            alert('File size exceeds 2MB limit.');
            return false;
        }

        return true;
    }

    function resetDropZone() {
        dropZone.querySelector('.drop-zone__prompt').innerHTML = `
            <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
            <p class="mb-1">Drag and drop your file here or click to browse</p>
            <small class="text-muted">Accepted formats: PDF, DOC, DOCX, XLSX (Max: 2MB)</small>
        `;
    }

    function updateThumbnail(dropZone, file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => {
                dropZone.querySelector('.drop-zone__prompt').innerHTML = `
                    <img src="${reader.result}" alt="${file.name}" class="drop-zone__thumb" data-label="${file.name}">
                    <p class="mb-1 mt-2">${file.name}</p>
                    <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                `;
            };
        } else {
            const fileIcon = getFileIcon(file.name);
            dropZone.querySelector('.drop-zone__prompt').innerHTML = `
                <i class="fas ${fileIcon} fa-2x mb-2 text-primary"></i>
                <p class="mb-1">${file.name}</p>
                <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
            `;
        }
    }

    function getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        switch (extension) {
            case 'pdf':
                return 'fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fa-file-word';
            case 'xlsx':
                return 'fa-file-excel';
            default:
                return 'fa-file';
        }
    }

    // Form submission handling
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default submission

        // Check if there are any report types available
        const hasReportTypes = Array.from(reportTypeSelect.options).some(option =>
            option.value !== '' && option.style.display !== 'none'
        );

        if (!hasReportTypes) {
            alert('You have already submitted all available reports.');
            return;
        }

        // Validate file
        if (!fileInput.files.length) {
            alert('Please select a file to upload.');
            return;
        }

        const file = fileInput.files[0];
        if (!validateFile(file)) {
            return;
        }

        // Validate report type
        if (!reportTypeSelect.value) {
            alert('Please select a report type.');
            return;
        }

        // Validate frequency-specific fields
        const frequency = reportTypeSelect.options[reportTypeSelect.selectedIndex].dataset.frequency;
        let isValid = true;

        switch (frequency) {
            case 'weekly':
                if (!form.querySelector('[name="month"]').value ||
                    !form.querySelector('[name="week_number"]').value ||
                    !form.querySelector('[name="num_of_clean_up_sites"]').value ||
                    !form.querySelector('[name="num_of_participants"]').value ||
                    !form.querySelector('[name="num_of_barangays"]').value ||
                    !form.querySelector('[name="total_volume"]').value) {
                    isValid = false;
                }
                break;
            case 'monthly':
                if (!form.querySelector('[name="month"]').value) {
                    isValid = false;
                }
                break;
            case 'quarterly':
                if (!form.querySelector('[name="quarter_number"]').value) {
                    isValid = false;
                }
                break;
            case 'semestral':
                if (!form.querySelector('[name="sem_number"]').value) {
                    isValid = false;
                }
                break;
        }

        if (!isValid) {
            alert('Please fill in all required fields for the selected report type.');
            return;
        }

        // Create FormData object
        const formData = new FormData(form);

        // Submit form using fetch
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show';
                successAlert.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                form.insertBefore(successAlert, form.firstChild);

                // Reset form
                form.reset();
                resetDropZone();

                // Close modal after 2 seconds
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('submitReportModal'));
                    modal.hide();
                    window.location.reload(); // Reload page to update the list
                }, 2000);
            } else {
                // Show error message
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                errorAlert.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                form.insertBefore(errorAlert, form.firstChild);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the report. Please try again.');
        });
    });
});
</script>
@endpush
@endsection
