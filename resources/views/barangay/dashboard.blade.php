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
            <!-- Remove the button or link that says 'Submit New Report' (the one that triggers the modal) from the dashboard. -->
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Recent Reports -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Reports</h5>
                    <a href="{{ route('barangay.submissions') }}" class="btn btn-link btn-sm p-0">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentReports->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No recent reports</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentReports as $report)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $report->reportType->name }}</h6>
                                            <small class="text-muted">Submitted: {{ $report->created_at->format('M d, Y') }}</small>
                                        </div>
                                        <span class="badge text-{{ $report->status === 'submitted' ? 'success' : 'warning' }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
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
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Upcoming Deadlines</h5>
                    <a href="{{ route('barangay.overdue-reports') }}" class="btn btn-link btn-sm p-0">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($upcomingDeadlines->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No upcoming deadlines</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($upcomingDeadlines as $deadline)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $deadline->name }}</h6>
                                            <small class="text-muted">Deadline: {{ $deadline->deadline->format('M d, Y') }}</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-{{ $deadline->frequency === 'weekly' ? 'info' :
                                                ($deadline->frequency === 'monthly' ? 'primary' :
                                                ($deadline->frequency === 'quarterly' ? 'success' :
                                                ($deadline->frequency === 'semestral' ? 'warning' : 'danger'))) }}">
                                                {{ ucfirst($deadline->frequency) }}
                                            </span>
                                            <button type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#submitReportModal"
                                                    data-report-type="{{ $deadline->id }}"
                                                    data-frequency="{{ $deadline->frequency }}">
                                                <i class="fas fa-file-upload me-1"></i>
                                                Submit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
                                <select id="frequency_filter" class="form-select" disabled>
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
                                <select id="report_type" class="form-select @error('report_type_id') is-invalid @enderror" name="report_type_id" required disabled>
                                    <option value="">Select Report Type</option>
                                    @foreach($reportTypes as $reportType)
                                        <option value="{{ $reportType->id }}" data-frequency="{{ $reportType->frequency }}">
                                            {{ $reportType->name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                        <small class="text-muted">Accepted formats: PDF, DOC, DOCX, XLSX (Max: 100MB)</small>
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

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === '') continue; // Skip the default option

            if (!selectedFrequency || option.dataset.frequency === selectedFrequency) {
                option.style.display = '';
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
        const maxSize = 100 * 1024 * 1024; // 100MB

        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        if (!validTypes.includes(fileExtension)) {
            alert('Invalid file type. Please upload PDF, DOC, DOCX, or XLSX files only.');
            return false;
        }

        if (file.size > maxSize) {
            alert('File size exceeds 100MB limit.');
            return false;
        }

        return true;
    }

    function resetDropZone() {
        dropZone.querySelector('.drop-zone__prompt').innerHTML = `
            <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
            <p class="mb-1">Drag and drop your file here or click to browse</p>
            <small class="text-muted">Accepted formats: PDF, DOC, DOCX, XLSX (Max: 100MB)</small>
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

        // Enable the report type select so its value is submitted
        reportTypeSelect.disabled = false;

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
